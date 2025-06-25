<?php

namespace App\Filament\Resources\ProjectResource\Forms;

use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Livewire;
use Filament\Forms\Components\CheckboxList;
use Awcodes\TableRepeater\Components\TableRepeater;
use Awcodes\TableRepeater\Header;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;

use App\Models\Material;
use App\Models\Modul;
use Filament\Forms\Get;
use App\Filament\Resources\ProjectResource\Forms\SelectComponents;

class ProjectFormSchema
{
    public static function getSchema(): array
    {
        $selectFields = config('project_fields.selectFields', []);
        $textInputs = config('project_fields.textInputs', []);

        return [
            Wizard::make([
                // Step 1: Informasi Proyek dan Referensi Modul
                Wizard\Step::make('Informasi Proyek')
                    ->schema([
                        Section::make('Informasi Dasar')
                            ->schema([
                                DatePicker::make('date')->label('Tanggal')->required(),
                                TextInput::make('recap_number')->label('No Rekap')->required(),
                                TextInput::make('no_contract')->label('No Kontrak')->required(),
                                TextInput::make('nip')
                                    ->label('NIP')
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function (callable $set, $state) {

                                        $moduls = Modul::where('nip', $state)->get();

                                        if ($moduls->isNotEmpty()) {

                                            $modulCodes = $moduls->pluck('code_cabinet')->toArray();
                                            $set('modul_reference', $modulCodes);

                                            $firstModul = $moduls->first();
                                            $set('product_name', $firstModul->product_name);
                                            $set('project_name', $firstModul->project_name);
                                        } else {
                                            $set('modul_reference', []);
                                            $set('product_name', null);
                                            $set('project_name', null);
                                        }
                                    }),
                                TextInput::make('product_name')->label('Nama Produk')->required(),
                                TextInput::make('project_name')->label('Nama Proyek')->required(),
                                Select::make('estimator')
                                    ->label("Estimator")
                                    ->options(
                                        [
                                            'reza' => "Reza",
                                            'ade' => "Ade",
                                        ]
                                    )
                                    ->searchable()
                                    ->extraAttributes([
                                        'style' => 'border: none !important; border-radius: 0 !important;',
                                    ]),
                                Select::make('recap_coordinator')
                                    ->label("Kordinator Rekap")
                                    ->options(
                                        [
                                            'reza' => "Reza",
                                            'ade' => "Ade",
                                        ]
                                    )
                                    ->searchable()
                                    ->extraAttributes([
                                        'style' => 'border: none !important; border-radius: 0 !important;',
                                    ]),
                                CheckboxList::make('project_status')
                                    ->label('Status Proyek')
                                    ->options([
                                        'Pendingan' => 'Pendingan',
                                        'Anti Rayap' => 'Anti Rayap',
                                    ])
                                    ->required(),
                            ])->columns(2),

                        Section::make('Spesifikasi')
                            ->schema([
                                TableRepeater::make('product_spesification')
                                    ->streamlined()
                                    ->label('Spesifikasi Produk')
                                    ->headers([
                                        Header::make('Bahan')->width('50%'),
                                        Header::make('Spesifikasi')->width('50%'),
                                    ])
                                    ->schema([
                                        TextInput::make('key')->label('Bahan')
                                            ->extraAttributes([
                                                'style' => 'border: none !important; border-radius: 0 !important;',
                                            ]),
                                        TextInput::make('value')->label('Spesifikasi')
                                            ->extraAttributes([
                                                'style' => 'border: none !important; border-radius: 0 !important;',
                                            ]),
                                    ])
                                    ->default([
                                        ['key' => 'Kabinet 1', 'value' => 'Ply'],
                                        ['key' => 'Kabinet 2', 'value' => 'Ply+mdf hijau 1mk'],
                                        ['key' => 'Kabinet 3', 'value' => 'Ply+mdf hijau 2mk'],
                                        ['key' => 'Kabinet 4', 'value' => 'Mdf hijau'],
                                        ['key' => 'Kabinet 5', 'value' => 'Ply'],
                                        ['key' => 'Kabinet 6', 'value' => 'Blockboard'],
                                        ['key' => 'Kabinet 7', 'value' => 'Ply Bending'],
                                        ['key' => 'Kabinet 8', 'value' => 'UPVC'],
                                    ])
                                    ->minItems(1)
                                    ->addActionLabel('Tambah Data')
                                    ->columnSpanFull(),

                                TableRepeater::make('material_thickness_spesification')
                                    ->streamlined()
                                    ->label('Ketebalan Material')
                                    ->headers([
                                        Header::make('Komponen')->width('50%'),
                                        Header::make('Tebal')->width('50%'),
                                    ])
                                    ->schema([
                                        TextInput::make('key')->label('Komponen')
                                            ->extraAttributes([
                                                'style' => 'border: none !important; border-radius: 0 !important;',
                                            ]),
                                        TextInput::make('value')->label('Tebal')
                                            ->extraAttributes([
                                                'style' => 'border: none !important; border-radius: 0 !important;',
                                            ]),
                                    ])
                                    ->default([
                                        ['key' => 'Komponen Kabinet', 'value' => '18'],
                                        ['key' => 'Komponen laci', 'value' => '12'],
                                        ['key' => 'Dinding blk/dasar', 'value' => '6'],
                                        ['key' => 'Tatakan S/s', 'value' => '12'],
                                        ['key' => 'Tatakan worktop', 'value' => '9'],
                                    ])
                                    ->addActionLabel('Tambah Data')
                                    ->columnSpanFull(),

                                TableRepeater::make('coating_standard')
                                    ->streamlined()
                                    ->label('Standar Lapisan')
                                    ->headers([
                                        Header::make('Kategori')->width('50%'),
                                        Header::make('Jenis')->width('50%'),
                                    ])
                                    ->schema([
                                        SelectComponents::materialCategorySelect("Kategori"),
                                        SelectComponents::materialNameSelect("Jenis"),
                                    ])
                                    ->default([
                                        ['key' => 'Lapisan dibalik pintu', 'value' => 'HMR MTC_01037_005'],
                                        ['key' => 'Lapisan tidak terlihat u/kab', 'value' => 'Polos'],
                                        ['key' => 'Lapisan depan bhn pintu mlp', 'value' => 'Polos'],
                                    ])
                                    ->minItems(1)
                                    ->addActionLabel('Tambah Data')
                                    ->columnSpanFull(),

                                TableRepeater::make('coating_spesification')
                                    ->streamlined()
                                    ->label('Spesifikasi Lapisan')
                                    ->streamlined()
                                    ->headers([
                                        Header::make('Kategori')->width('20%'),
                                        Header::make('Jenis')->width('55%'),
                                        Header::make('Val')->width('5%'),
                                        Header::make('Note')->width('30%'),
                                    ])
                                    ->schema([
                                        SelectComponents::materialCategorySelect("Kategori"),
                                        SelectComponents::materialNameSelect("Jenis"),
                                        TextInput::make('val')->label('Val')
                                            ->extraAttributes([
                                                'style' => 'border: none !important; border-radius: 0 !important;',
                                            ]),
                                        TextInput::make('note')->label('Note')
                                            ->extraAttributes([
                                                'style' => 'border: none !important; border-radius: 0 !important;',
                                            ]),
                                    ])
                                    ->default([
                                        ['key' => 'HPL', 'value' => 'HMR MTC_01037_005', 'val' => '1', 'note' => 'kabinet1'],
                                        ['key' => 'Edg', 'value' => 'Edg_Décor_1740_B', 'note' => 'edgingkab1'],
                                        ['key' => 'HPL', 'value' => 'Aica', 'val' => '0.5', 'note' => 'kabinet2'],
                                        ['key' => 'Edg', 'value' => '', 'note' => 'edgingkab2'],
                                        ['key' => 'HPL', 'value' => 'WYA_5297_E(V)', 'val' => '0.5', 'note' => 'kabinet3'],
                                        ['key' => 'Edg', 'value' => 'Edg_EAW_5297_E1', 'note' => 'edgingkab3'],
                                        ['key' => 'HPL', 'value' => '(WYA_5297_E(V) rangka……+Aica)', 'val' => '1', 'note' => 'lapisan1'],
                                        ['key' => 'Edg', 'value' => 'Edg_EAW_5297_E1(44X1)', 'note' => 'edging1'],
                                        ['key' => 'HPL', 'value' => '', 'val' => '1', 'note' => 'lapisan2'],
                                        ['key' => 'Edg', 'value' => 'Edg_EAP_1331_KO', 'note' => 'edging2'],
                                        ['key' => 'HPL', 'value' => '(0 rangka……+Aica)', 'val' => '1', 'note' => 'lapisan3'],
                                        ['key' => 'Edg', 'value' => 'Edg_HPL_(touch_up)', 'note' => 'edging3'],
                                        ['key' => 'HPL', 'value' => '', 'val' => '1', 'note' => 'lapisan4'],
                                        ['key' => 'Edg', 'value' => 'Edg_EAP_1358_MO', 'note' => 'edging4'],
                                        ['key' => 'HPL', 'value' => '', 'val' => '1', 'note' => 'lapisan5'],
                                        ['key' => 'Edg', 'value' => '', 'note' => 'edging5'],
                                        ['key' => 'HPL', 'value' => 'HMR Duco',  'val' => '1', 'note' => 'lapisan6'],
                                        ['key' => 'Edg', 'value' => 'Melanor',  'note' => 'edging6'],
                                    ])
                                    ->minItems(1)
                                    ->addActionLabel('Tambah Data')
                                    ->columnSpanFull(),

                                TableRepeater::make('komp_anodize_spesification')
                                    ->streamlined()
                                    ->label('Komp / Anodize')
                                    ->headers([
                                        Header::make('Kategori')->width('50%'),
                                        Header::make('Nama barang')->width('50%'),
                                    ])
                                    ->schema([
                                        SelectComponents::materialCategorySelect("Kategori"),
                                        SelectComponents::materialNameSelect("Nama barang"),
                                    ])
                                    ->default([
                                        ['key' => 'trim_21', 'value' => 'Trim 21 S2/S4 Black Gloss ( Alm. 75181 ) P3'],
                                        ['key' => 'trim_22', 'value' => 'Trim 22 S2/S4 Black Gloss ( Alm. 75270 ) P3'],
                                        ['key' => 'trim_38', 'value' => 'ST-36 Black Gloss ( Alm. 2351 ) P3 P3'],
                                        ['key' => 'mled_02', 'value' => 'M-LED-02 Black ( Alm. 75111 ) P3'],
                                        ['key' => 'mprf_01', 'value' => 'M-PRF-01 Black ( Alm. 75110 ) P3'],
                                        ['key' => 'mlis_01', 'value' => 'M-LIST-01 Black ( Alm. 75112 ) P3'],
                                        ['key' => 'lis_kaca', 'value' => 'List Kaca Black Gloss ( Alm. 6600/6599 ) P2.8'],
                                        ['key' => 'M_PLT_01', 'value' => 'M-PLT-01 Black Gloss ( Alm. 75256 ) P3'],
                                        ['key' => 'M_PLT_02', 'value' => 'M-PLT-02 Black Gloss ( Alm. 85830 ) P3'],
                                        ['key' => 'MH_02', 'value' => 'MH-02 Black ( Alm. 86706 ) P3'],
                                        ['key' => 'MH_02', 'value' => 'MH-02 Black ( Alm. 86706 ) P3 coak standart'],
                                        ['key' => 'MH_02', 'value' => 'MH-02 Black ( Alm. 86706 ) P3 coak khusus'],
                                        ['key' => 'MH_08', 'value' => 'MH-08 Brown Doff ( Alm. 75289 ) P3'],
                                        ['key' => 'MH_07', 'value' => 'MH-07 Brown ( Alm. 75272 ) P3'],
                                        ['key' => 'MH_04', 'value' => 'MH-04 Brown ( Alm. 86705 ) P3'],
                                        ['key' => 'MH_04', 'value' => 'MH-04 Brown ( Alm. 86705 ) P3 coak standart'],
                                        ['key' => 'MH_04', 'value' => 'MH-04 Brown ( Alm. 86705 ) P3 coak khusus'],
                                        ['key' => 'MH_09', 'value' => 'MH-09 A Champagne Gloss ( Alm. 75301 ) P3'],
                                        ['key' => 'MH_09', 'value' => 'MH-09 B Champagne Gloss ( Alm. MF 75303 R ) P3'],
                                    ])
                                    ->minItems(1)
                                    ->addActionLabel('Tambah Data')
                                    ->columnSpanFull(),

                                TableRepeater::make('alu_frame_spesification')
                                    ->streamlined()
                                    ->label('Aluminium Frame')
                                    ->headers([
                                        Header::make('Kategori')->width('50%'),
                                        Header::make('Nama barang')->width('50%'),
                                    ])
                                    ->schema([
                                        SelectComponents::materialCategorySelect("Kategori"),
                                        SelectComponents::materialNameSelect("Nama barang"),
                                    ])
                                    ->default([
                                        ['key' => 'm_frm_ttp_blk', 'value' => 'M-FRM Tutup Belakang Black Doff ( Alm. 75225 ) P3'],
                                        ['key' => 'm_frm', 'value' => 'M-FRM Body Black Doff ( Alm. 75226 ) P3'],
                                        ['key' => 'm_frm_07', 'value' => 'M-FRM-07 Black Doff ( Alm. 75355 ) P3'],
                                        ['key' => 'm_frm_02', 'value' => 'M-FRM-02 Black Doff ( Alm. 75227 ) P3'],
                                        ['key' => 'm_frm_03', 'value' => 'M-FRM-03 Black Doff ( Alm. 75229 ) P3'],
                                        ['key' => 'Mshf_0102', 'value' => 'M-SHF-01/02 Brown Doff  ( Alm. 75109 ) P3'],
                                        ['key' => 'LS_01', 'value' => 'LS-01 Brown Doff ( Alm. 86599 ) P3'],
                                        ['key' => 'm_frm_05', 'value' => 'M-FRM-05 Handle Brown Doff ( Alm. 75283 ) P3'],
                                        ['key' => 'm_frm_05', 'value' => 'M-FRM-05 Brown Doff ( Alm. 75284 ) P3'],
                                        ['key' => 'mulion_luar', 'value' => 'Mullion Luar Black Doff ( Alm. 75114 ) P3'],
                                        ['key' => 'mulion_dalam', 'value' => 'Mullion Dalam Black Doff ( Alm. 41316 ) P3'],
                                        ['key' => 'mulion_m_frm_07', 'value' => 'Alm. 75354 ( Mulion M-FRM-07 ) CA1 P6'],
                                        ['key' => 'LS_02', 'value' => 'LS-02 Black Gloss ( Alm. 10321 ) P3'],
                                        ['key' => 'mulion_dalam', 'value' => 'Mullion Dalam Black Doff ( Alm. 41316 ) P3'],
                                        ['key' => 'mulion_dalam', 'value' => 'Mullion Dalam Mocha Gloss ( Alm. 41316 ) P3'],
                                    ])
                                    ->minItems(1)
                                    ->addActionLabel('Tambah Data')
                                    ->columnSpanFull(),

                                TableRepeater::make('hinges_spesification')
                                    ->streamlined()
                                    ->label('Spesifikasi Engsel')
                                    ->headers([
                                        Header::make('Kategori')->width('50%'),
                                        Header::make('Nama barang')->width('50%'),
                                    ])
                                    ->schema([
                                        SelectComponents::materialCategorySelect("Kategori"),
                                        SelectComponents::materialNameSelect("Nama barang"),
                                    ])
                                    ->default([
                                        ['key' => 'engsel_pantry', 'value' => 'ENGSEL CLIP TOP  107 DEG INTEGRATED 75 B 1550 + 173 L 6100'],
                                        ['key' => 'engsel_pantry', 'value' => 'ENGSEL SALICE PUSH.TIP ON FOR DOOR'],
                                        ['key' => 'engsel_pantry', 'value' => 'ENGSEL 155 DRAJAT  71 B 7550'],
                                        ['key' => 'engsel_pantry', 'value' => 'ENGSEL BIFOLD'],
                                        ['key' => 'engsel_pantry', 'value' => 'ENGSEL 79M9550 BLIND CORNER'],
                                        ['key' => 'engsel_wrd', 'value' => 'ENGSEL 9936 W45 C6'],
                                        ['key' => 'engsel_wrd', 'value' => 'Engsel Sensys Full Bengkok C 16-110 DERAJAT 907 1207'],
                                        ['key' => 'engsel_wrd', 'value' => 'ENGSEL MEPLA FLAP DOOR'],
                                        ['key' => 'engsel_wrd', 'value' => 'M-FRM-05 Brown Doff ( Alm. 75284 ) P3'],
                                    ])
                                    ->minItems(1)
                                    ->addActionLabel('Tambah Data')
                                    ->columnSpanFull(),

                                TableRepeater::make('rail_spesification')
                                    ->streamlined()
                                    ->label('Spesifikasi Rel Laci')
                                    ->headers([
                                        Header::make('Kategori')->width('50%'),
                                        Header::make('Nama barang')->width('50%'),
                                    ])
                                    ->schema([
                                        SelectComponents::materialCategorySelect("Kategori"),
                                        SelectComponents::materialNameSelect("Nama barang"),
                                    ])
                                    ->default([
                                        ['key' => 'legrabox', 'value' => 'LEGRA S1 ORION GREY (OG'],
                                        ['key' => 'legrabox', 'value' => 'LEGRABOX S4 OG (ORION GREY)'],
                                        ['key' => 'rel_pantry', 'value' => 'REL TANDEM BLUM FULL EXT INTG P.500'],
                                        ['key' => 'rel_pantry', 'value' => 'REL TANDEM BLUM SINGLE EXT INTG P.500'],
                                        ['key' => 'tandembox', 'value' => 'LACI B1S1 INTE BLUMO'],
                                        ['key' => 'tandembox', 'value' => 'LACI B1S3 GREY+BM'],
                                        ['key' => 'legrabox', 'value' => 'LEGRABOX I6 ORION GREY'],
                                        ['key' => 'rel_pantry', 'value' => 'REL TANDEM BLUM SINGLE EXT INTG P.450'],
                                        ['key' => 'rel_pantry', 'value' => 'REL TANDEM BLUM SINGLE EXT NON INTG P.300'],
                                        ['key' => 'rel_pantry', 'value' => 'REL TANDEM BLUM FULL EXT NON INTG P.500'],
                                        ['key' => 'merivobox', 'value' => 'MERIVOBOX MVX S4 SW 500 70 BM'],
                                        ['key' => 'merivobox', 'value' => 'MERIVOBOX MVX S1 SW 500 40 BM'],
                                        ['key' => 'merivobox', 'value' => 'MERIVOBOX MVX i3 SW 500 40 BM'],
                                        ['key' => 'merivobox', 'value' => 'MERIVOBOX MVX i6 SW 500 70 BM'],
                                    ])
                                    ->minItems(1)
                                    ->addActionLabel('Tambah Rel')
                                    ->columnSpanFull(),

                                TableRepeater::make('door_mechanism')
                                    ->streamlined()->label('Spesifikasi Door Mechanism')
                                    ->headers([
                                        Header::make('Tipe')->width('50%'),
                                        Header::make('Nama barang')->width('50%'),
                                    ])
                                    ->schema([
                                        TextInput::make('key')->label('Tipe')->extraAttributes([
                                            'style' => 'border: none !important; border-radius: 0 !important;',
                                        ]),
                                        Select::make('value')->label('Nama barang')->options(Material::pluck('name', 'name'))->searchable()->extraAttributes([
                                            'style' => 'border: none !important; border-radius: 0 !important;',
                                        ]),
                                    ])
                                    ->default([
                                        ['key' => '', 'value' => 'POCKET DOOR 55 CM'],
                                        ['key' => 'aventos', 'value' => '-'],
                                        ['key' => 'aventos', 'value' => '-'],
                                        ['key' => 'aventos', 'value' => '-'],
                                    ])
                                    ->minItems(2)
                                    ->addActionLabel('Tambah Data')
                                    ->columnSpanFull(),

                                TableRepeater::make('glass_spesification')
                                    ->streamlined()
                                    ->label('Kaca')
                                    ->headers([
                                        Header::make('Tipe')->width('50%'),
                                        Header::make('Nama barang')->width('50%'),
                                    ])
                                    ->schema([
                                        TextInput::make('key')->label('Tipe')->extraAttributes([
                                            'style' => 'border: none !important; border-radius: 0 !important;',
                                        ]),
                                        Select::make('value')->label('Nama barang')->options(Material::pluck('name', 'name'))->searchable()->extraAttributes([
                                            'style' => 'border: none !important; border-radius: 0 !important;',
                                        ]),
                                    ])
                                    ->default([
                                        ['key' => 'Kaca type 1', 'value' => 'MG_Silver_Grey'],
                                        ['key' => 'Kaca type 2', 'value' => 'Euro_Grey'],
                                        ['key' => 'Kaca type 3', 'value' => 'Polos'],
                                        ['key' => 'Kaca type 4', 'value' => 'MG_Fenix'],
                                        ['key' => 'Kaca type 5', 'value' => 'Bronze'],
                                        ['key' => 'Kaca type 6', 'value' => 'Grey_Tinted'],
                                        ['key' => 'Kaca type 7', 'value' => 'Polos'],
                                        ['key' => 'Kaca type 8', 'value' => 'Moru↔'],
                                    ])
                                    ->minItems(5)
                                    ->addActionLabel('Tambah Data')
                                    ->columnSpanFull(),

                                TableRepeater::make('profile_spesification')
                                    ->streamlined()
                                    ->label('Profil')
                                    ->headers([
                                        Header::make('Kategori')->width('50%'),
                                        Header::make('Nama barang')->width('50%'),
                                    ])
                                    ->schema([
                                        SelectComponents::materialCategorySelect("Kategori"),
                                        SelectComponents::materialNameSelect("Nama barang"),
                                    ])
                                    ->default([
                                        ['key' => 'MR_02', 'value' => 'PROFIL R5 MDF hijau 10X15X2350 (MR 02)'],
                                        ['key' => 'Ornamen_Bunga', 'value' => 'PROFIL ORNAMEN MOTIF BUNGA 63X63(MOK-10)'],
                                        ['key' => 'MLP_02', 'value' => 'PROFIL MLP 02 MDF HIJAU UK 2350X16X17( Tidak coak)'],
                                        ['key' => 'MLT_01', 'value' => 'PROFIL MLT 01 MDF + PLY 2440X128X24'],
                                        ['key' => 'MR_02', 'value' => 'PROFIL R5 MDF hijau 10X15X2350 (MR 02)'],
                                        ['key' => 'MR_02', 'value' => 'PROFIL R5 MDF hijau 10X15X2350 (MR 02)'],
                                        ['key' => 'MPP_01', 'value' => 'PROFIL PLINT MPP-01 T.100  MDF HIJAU'],
                                        ['key' => 'MPP_01', 'value' => 'PROFIL PLINT MPP-01 T.100  MDF HIJAU'],
                                        ['key' => 'MPP_01', 'value' => 'PROFIL PLINT MPP-01 T.100  MDF HIJAU'],
                                    ])
                                    ->minItems(1)
                                    ->addActionLabel('Tambah Data')
                                    ->columnSpanFull(),

                                TableRepeater::make('size_distance_spesification')
                                    ->streamlined()
                                    ->label('Referensi Jarak Ukuran')
                                    ->headers([
                                        Header::make('Komponen')->width('50%'),
                                        Header::make('Jarak (mm)')->width('50%'),
                                    ])
                                    ->schema([
                                        TextInput::make('key')->label('Komponen')->extraAttributes([
                                            'style' => 'border: none !important; border-radius: 0 !important;',
                                        ]),
                                        TextInput::make('value')->label('Jarak (mm)')->extraAttributes([
                                            'style' => 'border: none !important; border-radius: 0 !important;',
                                        ]),
                                    ])
                                    ->default([
                                        ['key' => 'nat pintu 1 sisi', 'value' => '1.5'],
                                        ['key' => 'tebal cover panel/pelmet', 'value' => '19'],
                                        ['key' => 'nat antara pintu FC dg Worktop (MH03/09)', 'value' => '30'],
                                        ['key' => 'jarak pintu overlap atas', 'value' => '0'],
                                        ['key' => 'jarak pintu overlap bawah', 'value' => '100'],
                                    ])
                                    ->minItems(1)
                                    ->addActionLabel('Tambah Ukuran')
                                    ->columnSpanFull(),
                            ])
                            ->columns(1),
                        Select::make('modul_reference')
                            ->label('Referensi Modul')
                            ->multiple()
                            ->options(Modul::pluck('code_cabinet', 'code_cabinet')->toArray())
                            ->searchable()
                            ->preload()
                            ->reactive()
                            ->required()
                            ->hint('Pilih satu atau lebih modul sebagai referensi')
                            ->createOptionForm([
                                Section::make('Informasi Data')
                                    ->schema($textInputs)
                                    ->collapsible(),
                                TextInput::make('code_cabinet')
                                    ->label('Kode Cabinet')
                                    ->readOnly()
                                    ->dehydrated(true)
                                    ->required()
                                    ->rule('unique:moduls,code_cabinet')
                                    ->validationMessages([
                                        'unique' => 'Kode Cabinet tersebut sudah pernah dibuat.',
                                        'required' => 'Kode Cabinet wajib diisi.',
                                    ]),
                                Grid::make(2)->schema(
                                    collect($selectFields)->map(function ($config, $field) {
                                        return Select::make($field)
                                            ->label($config['label'])
                                            ->options(fn() => $config['model']::pluck('name', 'name'))
                                            ->searchable()
                                            ->reactive()
                                            ->afterStateUpdated(function (callable $set, callable $get) {
                                                $set('code_cabinet', generateCabinetCode(
                                                    $get('description_unit'),
                                                    $get('box_carcase_shape'),
                                                    finishing: $get('finishing'),
                                                    layerposition: $get('layer_position'),
                                                    boxContent: $get('box_carcase_content'),
                                                    closingSystem: $get('closing_system'),
                                                    numberOfClosures: $get('number_of_closures'),
                                                    typeOfClosure: $get('type_of_closure'),
                                                    handle: $get('handle'),
                                                    accessories: $get('acc'),
                                                    lamp: $get('lamp'),
                                                    plinth: $get('plinth'),
                                                ));
                                            });
                                    })->toArray()
                                )
                            ])
                            ->createOptionUsing(function (array $data) {
                                $modul = Modul::create($data);
                                return $modul->code_cabinet;
                            })
                            ->createOptionAction(function ($record, callable $set, callable $get) {
                                if (! $record instanceof Modul) {
                                    return;
                                }

                                $existing = $get('modul_reference');
                                if (!is_array($existing)) {
                                    $existing = [];
                                }


                                $existing = array_unique(array_merge($existing, [$record->id]));
                                dd($existing, $record->id);
                                $set('modul_reference', $existing);
                            })
                    ]),

                // Step 2: Breakdown Modul + Komponen
                Wizard\Step::make('Breakdown Modul')
                    ->schema([
                        Livewire::make('komponen-table')
                            ->data(function ($get, $livewire) {
                                return [
                                    'moduls' => $get('modul_reference') ?? [],
                                    'recordId' => $livewire->getRecord()?->id,
                                ];
                            }),
                    ]),
            ])
                ->startOnStep(
                    request()->routeIs('filament.admin.resources.projects.edit') && request()->get('step') === '2' ? 2 : 1
                )
                ->columnSpanFull(),
        ];
    }
}
