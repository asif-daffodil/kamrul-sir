<div class="max-w-md mx-auto px-4 sm:px-6 py-10 sm:py-16">

    <div class="bg-white rounded-3xl border border-stone-200 p-8 sm:p-10 shadow-xl space-y-6">

        <!-- Header -->
        <div class="text-center space-y-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-rose-50 text-rose-900 text-xs font-bold uppercase tracking-wider">
                👠 Customer Portal
            </span>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 font-serif">
                Welcome Back
            </h1>
            <p class="text-xs text-stone-500">
                Sign in with your phone or email to track orders and manage size exchanges.
            </p>
        </div>

        @if($errorMessage)
            <div class="p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-xs text-rose-800 flex items-start gap-2.5">
                <span class="text-base shrink-0">⚠️</span>
                <span>{{ $errorMessage }}</span>
            </div>
        @endif

        <form wire:submit.prevent="login" class="space-y-4">

            <!-- Identifier (Phone or Email) -->
            <div class="space-y-1.5">
                <label for="login_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                    Mobile Phone or Email <span class="text-rose-600">*</span>
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-stone-400 text-sm">
                        📱
                    </span>
                    <input
                        type="text"
                        id="login_id"
                        wire:model="login_id"
                        placeholder="01712345678 or name@example.com"
                        class="w-full pl-10 pr-3 py-3 rounded-xl border border-stone-200 text-xs sm:text-sm text-slate-900 focus:outline-hidden focus:ring-2 focus:ring-rose-800"
                        autofocus
                    >
                </div>
                @error('login_id')
                    <p class="text-xs text-rose-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password -->
            <div class="space-y-1.5">
                <div class="flex items-center justify-between">
                    <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                        Password <span class="text-rose-600">*</span>
                    </label>
                </div>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-stone-400 text-sm">
                        🔒
                    </span>
                    <input
                        type="password"
                        id="password"
                        wire:model="password"
                        placeholder="••••••••"
                        class="w-full pl-10 pr-3 py-3 rounded-xl border border-stone-200 text-xs sm:text-sm text-slate-900 focus:outline-hidden focus:ring-2 focus:ring-rose-800"
                    >
                </div>
                @error('password')
                    <p class="text-xs text-rose-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Remember me -->
            <div class="flex items-center justify-between text-xs pt-1">
                <label class="flex items-center gap-2 cursor-pointer select-none text-slate-700">
                    <input
                        type="checkbox"
                        wire:model="remember"
                        class="w-4 h-4 rounded text-rose-800 focus:ring-rose-800 border-stone-300"
                    >
                    <span>Remember me on this device</span>
                </label>
            </div>

            <!-- Submit Button -->
            <button
                type="submit"
                wire:loading.attr="disabled"
                class="w-full py-3.5 rounded-xl bg-rose-900 hover:bg-rose-800 text-white font-bold text-xs sm:text-sm uppercase tracking-wider transition shadow-lg hover:shadow-rose-900/30 flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50"
            >
                <span wire:loading.remove wire:target="login">Sign In to Account</span>
                <span wire:loading wire:target="login" class="flex items-center gap-2">
                    <span class="animate-spin inline-block">⏳</span> Signing in...
                </span>
            </button>

        </form>

        <!-- Divider & Registration Link -->
        <div class="pt-4 border-t border-stone-100 text-center space-y-3">
            <p class="text-xs text-stone-600">
                Don't have an account yet?
                <a href="{{ route('register') }}" class="font-bold text-rose-900 hover:underline">
                    Create New Account →
                </a>
            </p>

            <div class="p-3 rounded-xl bg-stone-50 border border-stone-100 text-[11px] text-stone-500 text-center">
                <span>Want to quickly check a guest order?</span><br>
                <a href="{{ route('track-order') }}" class="font-bold text-slate-800 hover:text-rose-900 underline mt-0.5 inline-block">
                    Track Order with Order ID & Phone →
                </a>
            </div>
        </div>

    </div>

</div>
