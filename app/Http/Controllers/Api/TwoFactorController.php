<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class TwoFactorController
{
    // Activer le 2FA
    public function enable(Request $request)
    {
        $user = Auth::user();

        // Générer un secret
        $secret = Str::random(32);

        $user->two_factor_secret = $secret;
        $user->two_factor_confirmed_at = null;
        $user->save();

        // Générer l'URL pour le QR code (API externe)
        $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode('otpauth://totp/IKABOUTIQUE:' . $user->email . '?secret=' . $secret . '&issuer=IKABOUTIQUE');

        // Générer les codes de récupération
        $recoveryCodes = $this->generateRecoveryCodes();

        return response()->json([
            'message' => '2FA activé avec succès',
            'secret' => $secret,
            'qr_code_url' => $qrCodeUrl,
            'recovery_codes' => $recoveryCodes,
        ]);
    }

    // Vérifier le code 2FA
    public function verify(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = Auth::user();

        // Simuler la vérification (pour les tests)
        // Dans la vraie vie, on utiliserait un vrai package 2FA
        if ($validated['code'] === '123456') {
            $user->two_factor_confirmed_at = now();
            $user->save();

            return response()->json([
                'message' => 'Code 2FA vérifié avec succès',
            ]);
        }

        return response()->json([
            'message' => 'Code 2FA invalide',
        ], Response::HTTP_BAD_REQUEST);
    }

    // Désactiver le 2FA
    public function disable(Request $request)
    {
        $user = Auth::user();

        $user->two_factor_secret = null;
        $user->two_factor_confirmed_at = null;
        $user->save();

        return response()->json([
            'message' => '2FA désactivé avec succès',
        ]);
    }

    // Vérifier le statut du 2FA
    public function status()
    {
        $user = Auth::user();

        return response()->json([
            'enabled' => !is_null($user->two_factor_secret),
            'confirmed' => !is_null($user->two_factor_confirmed_at),
        ]);
    }

    // Générer des codes de récupération
    private function generateRecoveryCodes()
    {
        $codes = [];
        for ($i = 0; $i < 8; $i++) {
            $codes[] = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8));
        }
        return $codes;
    }
}
