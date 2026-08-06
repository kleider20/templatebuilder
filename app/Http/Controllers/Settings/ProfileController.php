<?php

namespace App\Http\Controllers\Settings;

use App\Actions\Settings\DeleteProfileAction;
use App\Actions\Settings\UpdateProfileAction;
use App\Data\Settings\ProfileData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileDeleteRequest;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;

class ProfileController extends Controller
{
    public function __construct(
        protected UpdateProfileAction $updateProfile,
        protected DeleteProfileAction $deleteProfile,
    ) {}

    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request): Response
    {
        $data = new ProfileData(
            mustVerifyEmail: $request->user() instanceof MustVerifyEmail,
            status: $request->session()->get('status'),
        );

        return Inertia::render('settings/profile', $data->toArray());
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $this->updateProfile->handle($request->user(), $request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Profile updated.')]);

        return to_route('profile.edit');
    }

    /**
     * Delete the user's profile.
     */
    public function destroy(ProfileDeleteRequest $request): RedirectResponse
    {
        $user = $request->user();

        try {
            $this->deleteProfile->handle($user);
        } catch (RuntimeException $e) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $e->getMessage()]);

            return back();
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
