<?php

use Livewire\Component;
use Livewire\Attributes\Validate;
use Livewire\Attributes\Layout;
use App\Models\Subscriber;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\SubscriberEmailVerificationMail;
use Illuminate\Support\Facades\Cache;

new #[Layout('layouts.public')] class extends Component
{
    public $email;

    #[Validate('required|string|size:6')]
    public $code;

    public function mount()
    {
        $this->email = session('email');

        if (!$this->email) {
            return $this->redirect(route('home'), navigate: true);
        }
    }

    public function verifyCode()
    {
        $this->validate();
        
        $email = Cache::get("verify-email-for-{$this->code}");

        if(!$email || $email !== $this->email) {
            $this->addError('code', 'The verification code is invalid or has expired.');
            return;
        }

        $subscriber = new Subscriber();
        $subscriber->email = $this->email;
        $subscriber->save();
        
        session()->flash('subscribe-success', 'Email verified successfully! Thanks for Subscribing!!');
        $this->redirect(route('home'), navigate:true);
    }

    public function resendCode()
    {
        $oldCode = Cache::get("verify-email-token-{$this->email}");

        if($oldCode){
            Cache::forget("verify-email-token-{$this->email}");
            Cache::forget("verify-email-for-{$oldCode}");
        }
        
        $newCode = Str::random(6);
        Cache::put("verify-email-for-{$newCode}", $this->email, 15 * 60);
        Cache::put("verify-email-token-{$this->email}", $newCode, 15 * 60);
        Mail::to($this->email)->send(new SubscriberEmailVerificationMail($newCode));
        session()->flash('success', 'A new verification code has been sent to your inbox!');
    }
};
?>

<div class="max-w-md mx-auto mt-16 p-8 bg-white rounded-2xl border border-gray-200 shadow-xl">
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-indigo-100 rounded-full mb-4">
            <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
        </div>
        <h2 class="text-2xl font-bold text-gray-900">Verify your email</h2>
        <p class="mt-4 text-gray-600">
            A code has been sent to <span class="font-semibold text-indigo-600">{{ $email }}</span>. 
            Please check your inbox or spam folder and type it in below.
        </p>
    </div>

    <!-- Verification Form -->
    <form wire:submit="verifyCode" class="space-y-6">
        {{-- <input type="hidden" name="email" value="{{ $email }}"> --}}
        
        <div>
            <label for="code" class="block text-sm font-medium text-gray-700 mb-2">Verification Code</label>
            <input 
                type="text" 
                id="code" 
                wire:model="code" 
                placeholder="######"
                maxlength="6"
                class="block w-full text-center text-2xl tracking-widest rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-3"
            >
            @error('code')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <button 
            type="submit" 
            class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors"
            wire:loading.attr="disabled"
        >
            <span wire:loading.remove wire:target="verifyCode">Verify Code</span>
            <span wire:loading wire:target="verifyCode" class="flex items-center gap-2">
                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Verifying...
            </span>
        </button>
    </form>

    
    <!-- Success Message -->
    @if (session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4" wire:transition>
            <p class="text-sm text-green-800">{{ session('success') }}</p>
        </div>
    @endif
    

    <!-- Resend Form -->
    <div class="mt-8 pt-6 border-t border-gray-100 text-center">
        <p class="text-sm text-gray-600 mb-4">Didn't receive the code?</p>
        <form wire:submit="resendCode">
            <button 
                type="submit" 
                class="text-indigo-600 hover:text-indigo-800 font-semibold text-sm focus:outline-none"
                wire:loading.attr="disabled"
            >
                <span wire:loading.remove wire:target="resendCode">Resend verification code</span>
                <span wire:loading wire:target="resendCode" class="italic text-gray-400">Sending new code...</span>
            </button>
        </form>
    </div>
</div>
