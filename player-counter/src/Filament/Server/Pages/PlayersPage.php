<?php

namespace Boy132\PlayerCounter\Filament\Server\Pages;

use App\Models\Server;
use App\Traits\Filament\BlockAccessInConflict;
use Boy132\PlayerCounter\PlayerCounterPlugin;
use Exception;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Pagination\LengthAwarePaginator;

class PlayersPage extends Page implements HasTable
{
    use BlockAccessInConflict;
    use InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon = 'tabler-users-group';

    protected static ?string $slug = 'players';

    protected static ?int $navigationSort = 30;

    public static function canAccess(): bool
    {
        /** @var Server $server */
        $server = Filament::getTenant();

        return parent::canAccess() && $server->allocation && PlayerCounterPlugin::getGameQuery($server)->exists();
    }

    public static function getNavigationLabel(): string
    {
        return trans('player-counter::query.players');
    }

    public static function getModelLabel(): string
    {
        return static::getNavigationLabel();
    }

    public static function getPluralModelLabel(): string
    {
        return static::getNavigationLabel();
    }

    public function getTitle(): string
    {
        return static::getNavigationLabel();
    }

    /**
     * @throws Exception
     */
    public function table(Table $table): Table
    {
        return $table
            ->records(function (?string $search, int $page, int $recordsPerPage) {
                /** @var Server $server */
                $server = Filament::getTenant();

                $players = [];

                /** @var ?GameQuery $gameQuery */
                $gameQuery = PlayerCounterPlugin::getGameQuery($server)->first();

                if ($gameQuery) {
                    $data = $gameQuery->runQuery($server->allocation);
                    $players = $data['players'];
                }

                if ($search) {
                    $players = array_filter($players, fn ($player) => str($player['player'])->contains($search, true));
                }

                return new LengthAwarePaginator(array_slice($players, ($page - 1) * $recordsPerPage, $recordsPerPage), count($players), $recordsPerPage, $page);
            })
            ->paginated([30, 60])
            ->contentGrid([
                'default' => 1,
                'lg' => 2,
                'xl' => 3,
            ])
            ->columns([
                Split::make([
                    ImageColumn::make('helm_avatar')
                        ->state(fn (array $record) => 'https://cravatar.eu/head/' . $record['player'] . '/256.png')
                        ->grow(false),
                    TextColumn::make('player')
                        ->searchable(),
                ]),
            ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                EmbeddedTable::make(),
            ]);
    }
}
