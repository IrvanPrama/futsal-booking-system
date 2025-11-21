<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class RequestMemberWidget extends Widget
{
    protected static string $view = 'filament.widgets.request-member-widget';

    public static function canView(): bool
    {
        $user = Auth::user();

        // Jika user tidak login → sembunyikan
        if (!$user) {
            return false;
        }

        // Sembunyikan jika role = 0
        if ($user->role == 2) {
            return true;
        }

        // Selain itu tampilkan
        return true;
    }
}
