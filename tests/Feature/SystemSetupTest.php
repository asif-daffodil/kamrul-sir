<?php

namespace Tests\Feature;

use App\Services\CurrencyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SystemSetupTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    /**
     * Test home page loads properly with currency and Livewire.
     */
    public function test_welcome_page_renders_successfully(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('৳');
    }

    /**
     * Test CurrencyService correctly formats prices in BDT.
     */
    public function test_currency_service_formats_bdt_correctly(): void
    {
        $currency = app(CurrencyService::class);

        $this->assertEquals('BDT', $currency->code());
        $this->assertEquals('৳', $currency->symbol());
        $this->assertEquals('৳ 2,450.00', $currency->format(2450));
        $this->assertEquals('৳ 99.50', $currency->format(99.5));
    }

    /**
     * Test Filament admin panel route is registered.
     */
    public function test_filament_admin_is_accessible(): void
    {
        $response = $this->get('/admin');

        // Filament redirects unauthenticated users to /admin/login
        $response->assertRedirect('/admin/login');
    }

    /**
     * Test MySQL database connectivity.
     */
    public function test_database_connection_is_working(): void
    {
        $result = DB::select('SELECT 1 as is_connected');

        $this->assertNotEmpty($result);
        $this->assertEquals(1, $result[0]->is_connected);
    }
}
