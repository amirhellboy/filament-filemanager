<?php

namespace Amirhellboy\FilamentFileManager\Http\Middleware;

use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Amirhellboy\FilamentTinymceEditor\Models\TinymcePermission;

class AccessPanelPermission
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        if (!$user) {
            abort(403);
        }

        if (method_exists($user, 'canAccessPanel')) {
            if (!$user->canAccessPanel(app(\Filament\Panel::class))) {
                abort(403);
            }
        } else {
            abort(403);
        }


        return $next($request);
    }
}
