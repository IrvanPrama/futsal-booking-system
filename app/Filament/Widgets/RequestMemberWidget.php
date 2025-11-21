<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\MemberResource;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth; // penting!

class RequestMemberWidget extends Widget
{
    protected static string $view = 'filament.widgets.request-member-widget';

    public static function canView(): bool
    {
        $user = Auth::user();

        // hanya role 2 yang bisa melihat tombol
        return $user && $user->role == 2;
    }

    public function goToCreateMember()
    {
        return redirect(MemberResource::getUrl('create'));
    }
}
