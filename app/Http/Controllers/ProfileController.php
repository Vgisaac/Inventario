<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'avatar' => ['nullable', 'image', 'max:10240'],
        ]);

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            try {
                $file = $request->file('avatar');
                $filename = 'avatar' . '-' . time() . '.' . $file->getClientOriginalExtension();

                // Asegurar que la carpeta existe
                if (!Storage::disk('public')->exists('avatars')) {
                    Storage::disk('public')->makeDirectory('avatars');
                }

                $file->move(storage_path('app/public/avatars'), $filename);
                $user->avatar = 'avatars/' . $filename;

                Log::info("Avatar guardado vía Controller en: avatars/" . $filename);
            } catch (\Exception $e) {
                Log::error("Error en ProfileController: " . $e->getMessage());
                return back()->withErrors(['avatar' => 'Error al procesar la imagen.']);
            }
        }

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return back()->with('status', 'profile-updated');
    }
}
