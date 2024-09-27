<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VisitorResource\Pages;
use App\Filament\Resources\VisitorResource\RelationManagers;
use App\Models\Visitor;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class VisitorResource extends Resource
{
    protected static ?string $model = Visitor::class;

    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static ?string $navigationGroup = "Accueil";
    protected static ?string $modelLabel = "Visiteur";

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nom')
                    ->required(),
                Forms\Components\TextInput::make('prenom')
                    ->required(),
                Forms\Components\DatePicker::make('date_de_naissance')
                    ->required(),
                Forms\Components\Toggle::make('confirmed')
                    ->required(),
                Forms\Components\TextInput::make('email')
                    ->email(),
                Forms\Components\TextInput::make('phone')
                    ->tel(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nom')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('prenom')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('date_de_naissance')
                    ->date()
                    ->sortable(),
                Tables\Columns\IconColumn::make('confirmed')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('sejours_count')
                    ->label("Séjours passés")
                    ->counts('sejours')
                    ->sortable()
                ,
                Tables\Columns\TextColumn::make('last_sejour.departure_date')
                    ->label("Dernier départ")
                    ->date()
                ,
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('confirmed')
                    ->label("Visiteurs confirmés uniquement")
                    ->default()
                    ->query(fn(Builder $query) => $query->where('confirmed', true))
                ,
                Tables\Filters\Filter::make('par_age')
                    ->label("Filtrer par âges")
                    ->form([
                        Forms\Components\TextInput::make('start_age')
                            ->label("Âge de début")
                            ->minValue(0)
                            ->integer()
                            ->columnSpan(1)
                        ,
                        Forms\Components\TextInput::make('end_age')
                            ->label("Âge de fin")
                            ->minValue(0)
                            ->integer()
                            ->columnSpan(1)
                        ,
                    ])->columns(2)
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['start_age'] && $data['end_age'],
                                fn(Builder $query, $date): Builder => $query->betweenAges($data['start_age'], $data['end_age']),
                            );
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (! $data['start_age'] || !$data['end_age']) {
                            return null;
                        }

                        return 'Âgés entre ' . $data['start_age'] . ' et ' . $data['end_age'] . ' ans';
                    })
                ,
                Tables\Filters\Filter::make('has_sejour_within_dates')
                    ->form([
                        Forms\Components\DatePicker::make('begin_sejour_date')
                            ->label("Entre le")
                            ->columnSpan(1)
                        ,
                        Forms\Components\DatePicker::make('end_sejour_date')
                            ->label("Jusqu'au")
                            ->default(today())
                            ->columnSpan(1)
                        ,
                    ])->columns(2)
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['begin_sejour_date'] && $data['end_sejour_date'],
                                fn(Builder $query): Builder => $query->whereHas('sejours', fn (Builder $q) => $q->withinDates($data['begin_sejour_date'], $data['end_sejour_date']) ),
                            );
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (! $data['begin_sejour_date'] || !$data['end_sejour_date']) {
                            return null;
                        }

                        return 'A séjourné entre le ' . Carbon::parse($data['begin_sejour_date'])->toFormattedDateString() . ' et le ' . Carbon::parse($data['end_sejour_date'])->toFormattedDateString();
                    })
                ,

            ], layout: FiltersLayout::Modal)
            ->filtersFormWidth(MaxWidth::FourExtraLarge)
            ->filtersFormSchema(fn (array $filters): array => [
                $filters['confirmed'],
                Fieldset::make("Filtrer par âge")
                    ->schema([
                        $filters['par_age'],
                    ])
                    ->columns(2)
                    ->columnSpanFull()
                ,
                Fieldset::make("Filtrer par dates de séjour")
                    ->schema([
                        $filters['has_sejour_within_dates'],
                    ])
                    ->columns(2)
                    ->columnSpanFull()
                ,

            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVisitors::route('/'),
            'create' => Pages\CreateVisitor::route('/create'),
            'view' => Pages\ViewVisitor::route('/{record}'),
            'edit' => Pages\EditVisitor::route('/{record}/edit'),
        ];
    }
}
