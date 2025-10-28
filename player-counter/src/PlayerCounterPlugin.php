<?php

namespace Boy132\PlayerCounter;

use App\Models\Server;
use Boy132\PlayerCounter\Models\EggGameQuery;
use Boy132\PlayerCounter\Models\GameQuery;
use Filament\Contracts\Plugin;
use Filament\Panel;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class PlayerCounterPlugin implements Plugin
{
    public function getId(): string
    {
        return 'player-counter';
    }

    public function register(Panel $panel): void
    {
        $id = str($panel->getId())->title();

        $panel->discoverResources(plugin_path($this->getId(), "src/Filament/$id/Resources"), "Boy132\\PlayerCounter\\Filament\\$id\\Resources");
        $panel->discoverPages(plugin_path($this->getId(), "src/Filament/$id/Pages"), "Boy132\\PlayerCounter\\Filament\\$id\\Pages");
        $panel->discoverWidgets(plugin_path($this->getId(), "src/Filament/$id/Widgets"), "Boy132\\PlayerCounter\\Filament\\$id\\Widgets");
    }

    public function boot(Panel $panel): void {}

    public static function getGameQuery(Server $server): HasOneThrough
    {
        return $server->egg->hasOneThrough(GameQuery::class, EggGameQuery::class, 'egg_id', 'id', 'id', 'game_query_id');
    }
}
