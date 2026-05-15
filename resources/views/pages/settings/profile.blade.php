<?php

use App\Concerns\ProfileValidationRules;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

new #[Title('Profile settings')] class extends Component {
    use ProfileValidationRules, WithFileUploads;

    public string $name = '';
    public string $email = '';
    public $avatar;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;

        // Si es una petición POST tradicional (fallback por error 500 de Livewire)
        if (request()->isMethod('post')) {
            $this->updateProfileInformation();
        }
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
        ]);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        Flux::toast(text: __('Profile updated.'));
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Flux::toast(text: __('A new verification link has been sent to your email address.'));
    }

    #[Computed]
    public function hasUnverifiedEmail(): bool
    {
        return Auth::user() instanceof MustVerifyEmail && !Auth::user()->hasVerifiedEmail();
    }

    #[Computed]
    public function showDeleteUser(): bool
    {
        return !Auth::user() instanceof MustVerifyEmail || (Auth::user() instanceof MustVerifyEmail && Auth::user()->hasVerifiedEmail());
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    @if (session('status') === 'profile-updated')
        <script>
            document.addEventListener('livewire:init', () => {
                Flux.toast({
                    variant: 'success',
                    text: '{{ __('Profile updated.') }}'
                });
            });
        </script>
    @endif

    <flux:heading class="sr-only">{{ __('Profile settings') }}</flux:heading>

    <x-pages::settings.layout :heading="__('Profile')" :subheading="__('Update your name and email address')">
        <form action="{{ route('profile.edit') }}" method="POST" enctype="multipart/form-data"
            class="my-6 w-full space-y-6">
            @csrf
            <div class="flex flex-col items-center" x-data="{ imageUrl: '{{ auth()->user()->getAvatarUrl() }}' }">
                <input type="file" id="avatar-upload" name="avatar" class="hidden" accept="image/*"
                    x-on:change="imageUrl = URL.createObjectURL($event.target.files[0])" />

                <label for="avatar-upload" class="relative cursor-pointer group">
                    <div
                        class="relative flex items-center justify-center size-20 rounded-full transition-colors
            border border-zinc-200 dark:border-white/10 hover:border-zinc-300 dark:hover:border-white/10
            bg-zinc-100 hover:bg-zinc-200 dark:bg-white/10 hover:dark:bg-white/15 overflow-hidden">

                        <template x-if="imageUrl">
                            <img :src="imageUrl" class="size-full object-cover rounded-full" />
                        </template>

                        <template x-if="!imageUrl">
                            <flux:icon name="user" variant="solid" class="text-zinc-500 dark:text-zinc-400" />
                        </template>
                        <div wire:loading wire:target="avatar"
                            class="absolute inset-0 bg-white/50 dark:bg-black/50 flex items-center justify-center">
                            <div
                                class="w-5 h-5 border-2 border-zinc-500 border-t-transparent rounded-full animate-spin">
                            </div>
                        </div>
                    </div>
                    <div
                        class="absolute bottom-0 right-0 bg-white dark:bg-zinc-800 rounded-full shadow-md border border-zinc-200 dark:border-white/10 p-0.5">
                        <flux:icon name="arrow-up-circle" variant="solid"
                            class="size-5 text-zinc-500 dark:text-zinc-400" />
                    </div>
                </label>
                @error('avatar')
                    <p class="text-sm text-red-600 dark:text-red-400 mt-2">{{ $message }}</p>
                @enderror
            </div>
            <flux:input wire:model="name" name="name" :label="__('Name')" type="text" required autofocus
                autocomplete="name" />

            <div>
                <flux:input wire:model="email" name="email" :label="__('Email')" type="email" required
                    autocomplete="email" />

                @if ($this->hasUnverifiedEmail)
                    <div>
                        <flux:text class="mt-4">
                            {{ __('Your email address is unverified.') }}

                            <flux:link class="text-sm cursor-pointer"
                                wire:click.prevent="resendVerificationNotification">
                                {{ __('Click here to re-send the verification email.') }}
                            </flux:link>
                        </flux:text>

                    </div>
                @endif
            </div>

            <div class="flex items-center gap-4">
                <flux:button variant="primary" type="submit" data-test="update-profile-button">
                    {{ __('Save') }}
                </flux:button>
            </div>
        </form>

        @if ($this->showDeleteUser)
            <livewire:pages::settings.delete-user-form />
        @endif
    </x-pages::settings.layout>

    @push('js')
        <script>
            document.addEventListener('livewire:init', () => {
                Livewire.on('livewire:upload-error', (event) => {
                    console.error('Error de subida de Livewire:', event);
                });
            });

            function previewImage(event, querySelector) {

                //Recuperamos el input que desencadeno la acción
                let input = event.target;

                //Recuperamos la etiqueta img donde cargaremos la imagen
                let imgPreview = document.querySelector(querySelector);

                // Verificamos si existe una imagen seleccionada
                if (!input.files.length) return

                //Recuperamos el archivo subido
                let file = input.files[0];

                //Creamos la url
                let objectURL = URL.createObjectURL(file);

                //Modificamos el atributo src de la etiqueta img
                imgPreview.src = objectURL;

            }
        </script>
    @endpush
</section>
