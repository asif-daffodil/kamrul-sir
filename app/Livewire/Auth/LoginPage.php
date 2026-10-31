<?php

namespace App\Livewire\Auth;

use App\Models\Setting;
use App\Models\User;
use App\Support\BangladeshLocations;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.storefront')]
#[Title('Customer Sign In | Heel Bari Bangladesh')]
class LoginPage extends Component
{
    public string $login_id = '';
    public string $password = '';
    public bool $remember = true;
    public ?string $errorMessage = null;

    protected function rules(): array
    {
        return [
            'login_id' => ['required', 'string', 'min:3', 'max:150'],
            'password' => ['required', 'string', 'min:6'],
        ];
    }

    protected function messages(): array
    {
        return [
            'login_id.required' => 'Please enter your mobile phone number or email address.',
            'password.required' => 'Please enter your account password.',
        ];
    }

    public function login(): mixed
    {
        $this->errorMessage = null;
        $this->validate();

        $throttleKey = Str::transliterate(Str::lower($this->login_id) . '|' . request()->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $this->errorMessage = "Too many login attempts. Please wait {$seconds} seconds before trying again.";
            return null;
        }

        $input = trim($this->login_id);
        $user = null;

        if (str_contains($input, '@')) {
            $user = User::where('email', strtolower($input))->first();
        } else {
            $normalizedPhone = BangladeshLocations::normalizePhone($input);
            $user = User::where('phone', $normalizedPhone)
                ->orWhere('phone', 'like', '%' . substr($normalizedPhone, -10))
                ->first();
        }

        if (! $user || ! Hash::check($this->password, $user->password)) {
            RateLimiter::hit($throttleKey);
            $this->errorMessage = 'The provided phone number/email or password does not match our records.';
            return null;
        }

        if (! $user->is_active) {
            $this->errorMessage = 'Your account has been deactivated. Please contact customer care for assistance.';
            return null;
        }

        RateLimiter::clear($throttleKey);

        Auth::login($user, $this->remember);
        session()->regenerate();

        return redirect()->intended(route('account.dashboard'));
    }

    public function render()
    {
        $storeSettings = [
            'shop_name' => Setting::get('shop_name', config('shop.brand_name', 'Heel Bari')),
            'support_phone' => Setting::get('support_phone', '+880 1700-000000'),
        ];

        return view('livewire.auth.login-page', [
            'storeSettings' => $storeSettings,
        ]);
    }
}
