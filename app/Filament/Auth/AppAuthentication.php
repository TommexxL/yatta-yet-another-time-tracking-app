<?php

namespace App\Filament\Auth;

use Filament\Auth\MultiFactor\App\AppAuthentication as BaseAppAuthentication;
use Filament\Facades\Filament;
use SensitiveParameter;

class AppAuthentication extends BaseAppAuthentication
{
    public function generateQrCodeDataUri(#[SensitiveParameter] string $secret): string
    {
        $user = Filament::auth()->user();

        $inlineQrCode = $this->google2FA->getQRCodeInline(
            $this->getBrandName(),
            $this->getHolderName($user),
            $secret,
        );

        if (str_starts_with($inlineQrCode, 'data:image/')) {
            return $inlineQrCode;
        }

        return 'data:image/svg+xml;base64,'.base64_encode($inlineQrCode);
    }
}
