<?php

namespace App\Http\Controllers\Settings;

use App\Actions\Settings\UpdatePasswordAction;
use App\Data\Settings\SecurityData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\PasswordUpdateRequest;
use App\Http\Requests\Settings\TwoFactorAuthenticationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Features;

class SecurityController extends Controller
{
    public function __construct(
        protected UpdatePasswordAction $updatePassword,
    ) {}

    /**
     * Show the user's security settings page.
     */
    public function edit(TwoFactorAuthenticationRequest $request): Response
    {
        $canManagePasskeys = Features::canManagePasskeys();

        $passkeys = $canManagePasskeys
            ? $request->user()
                ->passkeys()
                ->select(['id', 'name', 'credential', 'created_at', 'last_used_at'])
                ->latest()
                ->get()
                ->map(fn ($passkey) => [
                    'id' => $passkey->id,
                    'name' => $passkey->name,
                    'authenticator' => $passkey->authenticator,
                    'created_at_diff' => $passkey->created_at->diffForHumans(),
                    'last_used_at_diff' => $passkey->last_used_at?->diffForHumans(),
                ])
                ->values()
                ->all()
            : [];

        $twoFactorEnabled = null;
        $requiresConfirmation = null;

        if (Features::canManageTwoFactorAuthentication()) {
            $request->ensureStateIsValid();

            $twoFactorEnabled = $request->user()->hasEnabledTwoFactorAuthentication();
            $requiresConfirmation = Features::optionEnabled(Features::twoFactorAuthentication(), 'confirm');
        }

        $data = new SecurityData(
            canManageTwoFactor: Features::canManageTwoFactorAuthentication(),
            canManagePasskeys: $canManagePasskeys,
            passkeys: $passkeys,
            passwordRules: Password::defaults()->toPasswordRulesString(),
            twoFactorEnabled: $twoFactorEnabled,
            requiresConfirmation: $requiresConfirmation,
        );

        $payload = $data->toArray();

        if (! $data->canManageTwoFactor) {
            unset($payload['twoFactorEnabled'], $payload['requiresConfirmation']);
        }

        return Inertia::render('settings/security', $payload);
    }

    /**
     * Update the user's password.
     */
    public function update(PasswordUpdateRequest $request): RedirectResponse
    {
        $this->updatePassword->handle($request->user(), $request->password);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Password updated.')]);

        return back();
    }
}
