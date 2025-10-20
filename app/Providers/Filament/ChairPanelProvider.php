<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use App\Models\Conference;
use App\Filament\Chair\Pages\EditConferenceSettings;
use App\Filament\Chair\Resources\TenantResource;
use App\Filament\Chair\Pages\SwitchConference;
use Filament\Http\Middleware\ShareCookiesFromRequestWithFilament;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


class ChairPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->brandName('Multi Conference - Chair Dashboard')
            ->id('chair')
            ->path('chair')
            // ->login()
            ->tenant(Conference::class, slugAttribute: 'slug', 
            // ownershipRelationship: 'conferences'
            )

            //ini untuk menampilkan menu tenant di sidebar
            ->tenantMenu(true)
            ->renderHook('panels::sidebar.nav.start', function () {
                $t = Filament::getTenant();
                if (! $t) return '';

                $name = e($t->name ?? '—');
                $loc  = e($t->location ?? '');
                $logoUrl = null;

                if (!empty($t->logo)) {
                    if (Str::startsWith($t->logo, ['http://', 'https://', '/'])) {
                        $logoUrl = $t->logo;
                    } else {
                        $logoUrl = Storage::url($t->logo); // ambil dari storage/public
                    }
                }

                $initials = strtoupper(mb_substr($t->name ?? '—', 0, 2));

                $locHtml = $loc
                    ? '<div class="text-xs text-gray-500 dark:text-gray-400 break-words whitespace-normal">'.$loc.'</div>'
                    : '';

                $logoHtml = $logoUrl
                    ? '<img src="'.$logoUrl.'" alt="Logo" class="h-10 w-10 rounded-xl object-cover ring-1 ring-black/10 dark:ring-white/10">'
                    : '<div class="h-10 w-10 rounded-xl bg-zinc-900 text-white flex items-center justify-center text-sm font-semibold">'.$initials.'</div>';

                return <<<HTML
                <div class="px-4 py-3">
                    <div class="flex items-center gap-3">
                    {$logoHtml}
                    <div class="min-w-0">
                        <div class="text-sm font-semibold leading-tight break-words whitespace-normal" title="{$name}">
                        {$name}
                        </div>
                        {$locHtml}
                    </div>
                    </div>
                </div>
                HTML;
            })
            ->tenantMenuItems([])
            ->colors([
                'primary' => Color::Amber,
            ])
            ->authGuard('chair')
            ->discoverResources(in: app_path('Filament/Chair/Resources'), for: 'App\\Filament\\Chair\\Resources')
            ->discoverPages(in: app_path('Filament/Chair/Pages'), for: 'App\\Filament\\Chair\\Pages')
            ->pages([
                Pages\Dashboard::class,
                EditConferenceSettings::class,
                SwitchConference::class, 
            ])
            ->discoverWidgets(in: app_path('Filament/Chair/Widgets'), for: 'App\\Filament\\Chair\\Widgets')
            ->widgets([
                // Widgets\AccountWidget::class,
                // Widgets\FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
