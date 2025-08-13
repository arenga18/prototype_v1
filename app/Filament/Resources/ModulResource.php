<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ModulResource\Pages;
use App\Models\Accessories;
use App\Models\BoxCarcaseContent;
use App\Models\BoxCarcaseShape;
use App\Models\ClosingSystem;
use App\Models\DescriptionUnit;
use App\Models\Finishing;
use App\Models\Handle;
use App\Models\LayerPosition;
use App\Models\Modul;
use App\Models\NumberOfClosure;
use App\Models\TypeOfClosure;
use App\Models\Lamp;
use App\Models\Plinth;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;

class ModulResource extends Resource
{
    protected static ?string $model = Modul::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = "Modul";

    // Array konfigurasi Select field
    protected static $selectFields = [
        'description_unit' => ['label' => 'Deskripsi Unit', 'model' => DescriptionUnit::class],
        'box_carcase_shape' => ['label' => 'Bentuk Box/Carcase', 'model' => BoxCarcaseShape::class],
        'finishing' => ['label' => 'Finishing', 'model' => Finishing::class],
        'layer_position' => ['label' => 'Posisi Lapisan', 'model' => LayerPosition::class],
        'box_carcase_contents' => ['label' => 'Isi Box/Carcase', 'model' => BoxCarcaseContent::class],
        'closing_system' => ['label' => 'Sistem Tutup', 'model' => ClosingSystem::class],
        'number_of_closures' => ['label' => 'Jumlah Tutup', 'model' => NumberOfClosure::class],
        'type_of_closure' => ['label' => 'Jenis Tutup', 'model' => TypeOfClosure::class],
        'handle' => ['label' => 'Handle', 'model' => Handle::class],
        'acc' => ['label' => 'Accessories', 'model' => Accessories::class],
        'lamp' => ['label' => 'Lampu', 'model' => Lamp::class],
        'plinth' => ['label' => 'Plinth', 'model' => Plinth::class],
    ];

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Form $form): Form
    {
        $textInputs = [
            DatePicker::make('input_date')->label('Tanggal Input')->required(),
            TextInput::make('nip')->label('NIP')->required(),
            TextInput::make('height')->label('Tinggi')->required(),
            TextInput::make('project_name')->label('Nama Proyek')->required(),
            TextInput::make('product_name')->label('Nama Produk')->required(),
        ];

        $code_cabinet = TextInput::make('code_cabinet')
            ->label('Kode Cabinet')
            ->readOnly()
            ->columnSpanFull()
            ->required();

        $selectComponents = [];

        foreach (self::$selectFields as $field => $config) {
            $selectComponents[] = Select::make($field)
                ->label($config['label'])
                ->options(fn() => $config['model']::pluck('name', 'name'))
                ->reactive()
                ->searchable()
                ->afterStateUpdated(function (callable $set, callable $get, $state) {
                    $set('code_cabinet', generateCabinetCode(
                        $get('description_unit'),
                        $get('box_carcase_shape'),
                        finishing: $get('finishing'),
                        layerposition: $get('layer_position'),
                        boxContent: $get('box_carcase_contents'),
                        closingSystem: $get('closing_system'),
                        numberOfClosures: $get('number_of_closures'),
                        typeOfClosure: $get('type_of_closure'),
                        handle: $get('handle'),
                        accessories: $get('acc'),
                        lamp: $get('lamp'),
                        plinth: $get('plinth'),
                    ));
                });
        }

        // Gabungkan menjadi satu array
        $sectionComponents = array_merge([$code_cabinet], $selectComponents);


        return $form->schema([
            Section::make('Informasi Data')
                ->schema($textInputs)
                ->collapsible(),

            Section::make('Komponen')
                ->schema([

                    Grid::make()
                        ->columns(2)
                        ->schema($sectionComponents),
                ])
                ->collapsible(),
        ]);
    }



    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('input_date')
                    ->label("Tgl Input")
                    ->searchable(),
                TextColumn::make('nip')
                    ->label("NIP")
                    ->searchable(),
                TextColumn::make('height')
                    ->label("Tinggi")
                    ->searchable(),
                TextColumn::make('project_name')
                    ->label("Nama Projek")
                    ->searchable(),
                TextColumn::make('product_name')
                    ->label("Nama Product")
                    ->searchable(),
                TextColumn::make('code_cabinet')
                    ->label("Kode Kabinet")
                    ->searchable(),
                TextColumn::make('description_unit')
                    ->label("Deskripsi Unit")
                    ->searchable(),
                TextColumn::make('box_carcase_shape')
                    ->label("Bentuk Box/Carcase")
                    ->searchable(),
                TextColumn::make('finishing')
                    ->label("Finishing")
                    ->searchable(),
                TextColumn::make('layer_position')
                    ->label("Posisi Lapisan")
                    ->searchable(),
                TextColumn::make('box_carcase_content')
                    ->label("Isi Box/Carcase")
                    ->searchable(),
                TextColumn::make('closing_system')
                    ->label("Sistem Tutup")
                    ->searchable(),
                TextColumn::make('number_of_closures')
                    ->label("Jumlah Tutup")
                    ->searchable(),
                TextColumn::make('type_of_closure')
                    ->label("Jenis Tutup")
                    ->searchable(),
                TextColumn::make('handle')
                    ->label("Handle")
                    ->searchable(),
                TextColumn::make('acc')
                    ->label("Assesories")
                    ->searchable(),
                TextColumn::make('lamp')
                    ->label("Lampu")
                    ->searchable(),
                TextColumn::make('plinth')
                    ->label("Plinth")
                    ->searchable(),
            ])
            ->searchable()
            ->filters([
                ...collect(self::$selectFields)->map(function ($item, $field) {
                    return Filter::make($field)
                        ->form([
                            Select::make($field)
                                ->label($item['label'])
                                ->options(fn() => $item['model']::pluck('name', 'name'))
                                ->searchable(),
                        ])
                        ->query(function ($query, array $data) use ($field) {
                            return $query->when(
                                $data[$field] ?? null,
                                fn($q) => $q->where($field, $data[$field])
                            );
                        });
                })->values()->toArray(),
            ])->filtersFormColumns(2)->filtersFormMaxHeight('500px')

            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListModuls::route('/'),
            'create' => Pages\CreateModul::route('/create'),
            'edit' => Pages\EditModul::route('/{record}/edit'),
        ];
    }
}
