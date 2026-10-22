<?php

namespace App\Livewire\Auth;

use App\Models\Setting;
use App\Models\User;
use App\Rules\BangladeshPhone;
use App\Support\BangladeshLocations;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.storefront')]
#[Title('Create Customer Account | [BRAND_NAME] Bangladesh')]
class RegisterPage extends Component
{
    public string $name = '';
    public string $phone = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public bool $acceptTerms = true;

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:100'],
            'phone' => ['required', 'string', new BangladeshPhone()],
            'email' => ['nullable', 'email', 'max:150'],
            'password' => ['required', 'string', 'confirmed', Password::min(8)],
            'acceptTerms' => ['accepted'],
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'Please provide your full name.',
            'phone.required' => 'Please provide your 11-digit mobile phone number.',
            'password.required' => 'Please create a secure password (minimum 8 characters).',
            'password.confirmed' => 'The password confirmation does not match.',
            'acceptTerms.accepted' => 'You must agree to our store policies to register.',
        ];
    }

    public function register(): mixed
    {
        $this->validate();

        $normalizedPhone = BangladeshLocations::normalizePhone($this->phone);

        // Check unique phone among active users
        if (User::where('phone', $normalizedPhone)->exists()) {
            $this->addError('phone', 'This mobile number is already registered. Please sign in instead.');
            return null;
        }

        // Check unique email if provided
        $cleanEmail = ! empty($this->email) ? strtolower(trim($this->email)) : null;
        if ($cleanEmail && User::where('email', $cleanEmail)->exists()) {
            $this->addError('email', 'This email address is already associated with an account.');
            return null;
        }

        $user = User::create([
            'name' => trim($this->name),
            'phone' => $normalizedPhone,
            'email' => $cleanEmail,
            'password' => Hash::make($this->password),
            'role' => 'customer',
            'is_active' => true,
        ]);

        Auth::login($user);
        session()->regenerate();

        return redirect()->intended(route('account.dashboard'));
    }

    public function render()
    {
        $storeSettings = [
            'shop_name' => Setting::get('shop_name', config('shop.brand_name', '[BRAND_NAME]')),
            'support_phone' => Setting::get('support_phone', '+880 1700-000000'),
        ];

        return view('livewire.auth.register-page', [
            'storeSettings' => $storeSettings,
        ]);
    }
}
