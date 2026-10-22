<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class HealthCheckController extends Controller
{
    /**
     * Perform application system and dependency health checks.
     */
    public function __invoke(): JsonResponse
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'storage' => $this->checkStorage(),
        ];

        $isHealthy = collect($checks)->every(fn ($check) => $check['status'] === 'up');

        $statusCode = $isHealthy ? 200 : 503;

        return response()->json([
            'status' => $isHealthy ? 'healthy' : 'unhealthy',
            'timestamp' => now()->toIso8601String(),
            'app' => [
                'name' => config('app.name'),
                'environment' => app()->environment(),
                'version' => '1.0.0',
            ],
            'checks' => $checks,
        ], $statusCode);
    }

    protected function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();

            return [
                'status' => 'up',
                'connection' => config('database.default'),
            ];
        } catch (\Throwable $e) {
            Log::error('Health check failed for database', ['error' => $e->getMessage()]);

            return [
                'status' => 'down',
                'error' => $e->getMessage(),
            ];
        }
    }

    protected function checkCache(): array
    {
        try {
            $key = 'health_ping_' . microtime(true);
            Cache::put($key, 'pong', 5);
            $val = Cache::get($key);
            Cache::forget($key);

            if ($val !== 'pong') {
                return ['status' => 'down', 'error' => 'Cache ping value mismatch'];
            }

            return [
                'status' => 'up',
                'store' => config('cache.default'),
            ];
        } catch (\Throwable $e) {
            Log::error('Health check failed for cache', ['error' => $e->getMessage()]);

            return [
                'status' => 'down',
                'error' => $e->getMessage(),
            ];
        }
    }

    protected function checkStorage(): array
    {
        try {
            $disk = Storage::disk('public');
            $testFile = 'health_check_test.txt';
            $disk->put($testFile, 'health-ok');
            $read = $disk->get($testFile);
            $disk->delete($testFile);

            if ($read !== 'health-ok') {
                return ['status' => 'down', 'error' => 'Storage read mismatch'];
            }

            return [
                'status' => 'up',
                'disk' => 'public',
            ];
        } catch (\Throwable $e) {
            Log::error('Health check failed for storage', ['error' => $e->getMessage()]);

            return [
                'status' => 'down',
                'error' => $e->getMessage(),
            ];
        }
    }
}
