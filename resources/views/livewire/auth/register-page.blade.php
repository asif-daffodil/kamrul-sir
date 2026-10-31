<div class="max-w-md mx-auto px-4 sm:px-6 py-10 sm:py-16">

    <div class="bg-white rounded-3xl border border-stone-200 p-8 sm:p-10 shadow-xl space-y-6">

        <!-- Header -->
        <div class="text-center space-y-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-rose-50 text-rose-900 text-xs font-bold uppercase tracking-wider">
                ✨ Exclusive Membership
            </span>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 font-serif">
                Create Account
            </h1>
            <p class="text-xs text-stone-500">
                Join Heel Bari for faster checkout, live order tracking, and 7-day size exchanges.
            </p>
        </div>

        <form wire:submit.prevent="register" class="space-y-4">

            <!-- Name -->
            <div class="space-y-1.5">
                <label for="name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                    Full Name <span class="text-rose-600">*</span>
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-stone-400 text-sm">
                        👤
                    </span>
                    <input
                        type="text"
                        id="name"
                        wire:model="name"
                        placeholder="e.g. Mehazabien Chowdhury"
                        class="w-full pl-10 pr-3 py-3 rounded-xl border border-stone-200 text-xs sm:text-sm text-slate-900 focus:outline-hidden focus:ring-2 focus:ring-rose-800"
                        autofocus
                    >
                </div>
                @error('name')
                    <p class="text-xs text-rose-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Mobile Phone (BD) -->
            <div class="space-y-1.5">
                <label for="phone" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                    Mobile Phone Number <span class="text-rose-600">*</span>
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-stone-400 text-xs font-bold font-mono">
                        +88
                    </span>
                    <input
                        type="tel"
                        id="phone"
                        wire:model="phone"
                        placeholder="01712345678"
                        class="w-full pl-12 pr-3 py-3 rounded-xl border border-stone-200 text-xs sm:text-sm font-mono text-slate-900 focus:outline-hidden focus:ring-2 focus:ring-rose-800"
                    >
                </div>
                <p class="text-[10px] text-stone-400">Used for courier delivery confirmation and fast login.</p>
                @error('phone')
                    <p class="text-xs text-rose-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email (Optional) -->
            <div class="space-y-1.5">
                <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center justify-between">
                    <span>Email Address</span>
                    <span class="text-[10px] font-normal text-stone-400 normal-case">(Optional)</span>
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-stone-400 text-sm">
                        ✉️
                    </span>
                    <input
                        type="email"
                        id="email"
                        wire:model="email"
                        placeholder="name@example.com"
                        class="w-full pl-10 pr-3 py-3 rounded-xl border border-stone-200 text-xs sm:text-sm text-slate-900 focus:outline-hidden focus:ring-2 focus:ring-rose-800"
                    >
                </div>
                @error('email')
                    <p class="text-xs text-rose-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password -->
            <div class="space-y-1.5">
                <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                    Password <span class="text-rose-600">*</span>
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-stone-400 text-sm">
                        🔒
                    </span>
                    <input
                        type="password"
                        id="password"
                        wire:model="password"
                        placeholder="Minimum 8 characters"
                        class="w-full pl-10 pr-3 py-3 rounded-xl border border-stone-200 text-xs sm:text-sm text-slate-900 focus:outline-hidden focus:ring-2 focus:ring-rose-800"
                    >
                </div>
                @error('password')
                    <p class="text-xs text-rose-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password Confirmation -->
            <div class="space-y-1.5">
                <label for="password_confirmation" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                    Confirm Password <span class="text-rose-600">*</span>
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-stone-400 text-sm">
                        🔒
                    </span>
                    <input
                        type="password"
                        id="password_confirmation"
                        wire:model="password_confirmation"
                        placeholder="Re-type password"
                        class="w-full pl-10 pr-3 py-3 rounded-xl border border-stone-200 text-xs sm:text-sm text-slate-900 focus:outline-hidden focus:ring-2 focus:ring-rose-800"
                    >
                </div>
            </div>

            <!-- Terms -->
            <div class="pt-1">
                <label class="flex items-start gap-2.5 cursor-pointer select-none text-xs text-stone-600">
                    <input
                        type="checkbox"
                        wire:model="acceptTerms"
                        class="w-4 h-4 rounded text-rose-800 focus:ring-rose-800 border-stone-300 mt-0.5"
                    >
                    <span>
                        I agree to the <strong class="text-slate-900">7-Day Doorstep Size Exchange Policy</strong> and Customer Terms.
                    </span>
                </label>
                @error('acceptTerms')
                    <p class="text-xs text-rose-600 font-medium mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Submit Button -->
            <button
                type="submit"
                wire:loading.attr="disabled"
                class="w-full py-3.5 rounded-xl bg-rose-900 hover:bg-rose-800 text-white font-bold text-xs sm:text-sm uppercase tracking-wider transition shadow-lg hover:shadow-rose-900/30 flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50"
            >
                <span wire:loading.remove wire:target="register">Create My Account</span>
                <span wire:loading wire:target="register" class="flex items-center gap-2">
                    <span class="animate-spin inline-block">⏳</span> Creating account...
                </span>
            </button>

        </form>

        <!-- Sign in Link -->
        <div class="pt-4 border-t border-stone-100 text-center">
            <p class="text-xs text-stone-600">
                Already have an account?
                <a href="{{ route('login') }}" class="font-bold text-rose-900 hover:underline">
                    Sign In Here →
                </a>
            </p>
        </div>

    </div>

</div>
