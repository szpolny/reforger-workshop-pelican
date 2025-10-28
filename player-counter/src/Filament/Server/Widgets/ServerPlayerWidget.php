<?php

namespace Boy132\PlayerCounter\Filament\Server\Widgets;

use App\Filament\Server\Components\SmallStatBlock;
use App\Models\Server;
use Boy132\PlayerCounter\Models\GameQuery;
use Boy132\PlayerCounter\PlayerCounterPlugin;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget;

class ServerPlayerWidget extends StatsOverviewWidget
{
    protected ?string $pollingInterval = '30s';

    public static function canView(): bool
    {
        /** @var Server $server */
        $server = Filament::getTenant();

        return !$server->isInConflictState() && $server->allocation && PlayerCounterPlugin::getGameQuery($server)->exists() && !$server->retrieveStatus()->isOffline();
    }

    protected function getStats(): array
    {
        /** @var Server $server */
        $server = Filament::getTenant();

        /** @var ?GameQuery $gameQuery */
        $gameQuery = PlayerCounterPlugin::getGameQuery($server)->first();

        if (!$gameQuery) {
            return [];
        }

        $data = $gameQuery->runQuery($server->allocation);

        return [
            SmallStatBlock::make(trans('player-counter::query.hostname'), $data['gq_hostname'] ?? trans('player-counter::query.unknown')),
            SmallStatBlock::make(trans('player-counter::query.players'), ($data['gq_numplayers'] ?? '?') . ' / ' . ($data['gq_maxplayers'] ?? '?')),
            SmallStatBlock::make(trans('player-counter::query.map'), $data['gq_mapname'] ?? trans('player-counter::query.unknown')),
        ];
    }
}
