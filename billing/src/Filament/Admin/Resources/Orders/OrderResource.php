<?php

namespace Boy132\Billing\Filament\Admin\Resources\Orders;

use App\Filament\Admin\Resources\Servers\Pages\EditServer;
use App\Filament\Components\Tables\Columns\DateTimeColumn;
use Boy132\Billing\Enums\OrderStatus;
use Boy132\Billing\Filament\Admin\Resources\Customers\Pages\EditCustomer;
use Boy132\Billing\Filament\Admin\Resources\Orders\Pages\ListOrders;
use Boy132\Billing\Filament\Admin\Resources\Products\Pages\EditProduct;
use Boy132\Billing\Models\Order;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use NumberFormatter;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static string|\BackedEnum|null $navigationIcon = 'tabler-truck-delivery';

    protected static string|\UnitEnum|null $navigationGroup = 'Billing';

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getEloquentQuery()->count() ?: null;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('status')
                    ->sortable()
                    ->badge(),
                TextColumn::make('customer')
                    ->label('Customer')
                    ->icon('tabler-user-dollar')
                    ->sortable()
                    ->url(fn (Order $order) => EditCustomer::getUrl(['record' => $order->customer])),
                TextColumn::make('server.name')
                    ->label('Server')
                    ->placeholder('No server')
                    ->icon('tabler-brand-docker')
                    ->sortable()
                    ->url(fn (Order $order) => $order->server ? EditServer::getUrl(['record' => $order->server]) : null),
                TextColumn::make('productPrice.product.name')
                    ->label('Product')
                    ->icon('tabler-package')
                    ->sortable()
                    ->url(fn (Order $order) => EditProduct::getUrl(['record' => $order->productPrice->product])),
                TextColumn::make('productPrice.name')
                    ->label('Price')
                    ->sortable(),
                TextColumn::make('productPrice.cost')
                    ->label('Cost')
                    ->sortable()
                    ->formatStateUsing(function ($state) {
                        $formatter = new NumberFormatter(auth()->user()->language, NumberFormatter::CURRENCY);

                        return $formatter->formatCurrency($state, config('billing.currency'));
                    }),
                DateTimeColumn::make('expires_at')
                    ->label('Expires')
                    ->placeholder('No expire')
                    ->color(fn ($state) => $state <= now('UTC') ? 'danger' : null)
                    ->since(),
            ])
            ->recordActions([
                Action::make('activate')
                    ->visible(fn (Order $order) => $order->status !== OrderStatus::Active)
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn (Order $order) => $order->activate()),
                Action::make('close')
                    ->visible(fn (Order $order) => $order->status === OrderStatus::Active)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(fn (Order $order) => $order->close()),
            ])
            ->emptyStateHeading('No Orders')
            ->emptyStateDescription('');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrders::route('/'),
        ];
    }
}
