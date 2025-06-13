<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\PartComponent;
use Illuminate\Http\Request;

class PartComponentLivewire extends Component
{
    public $moduls = [];
    public $partComponentsData = [];
    public $dataValidationCol;
    public $partData;
    public $dataValMap;
    public $recordId;

    public function mount($recordId = null)
    {
        $this->dataValidationCol = config("breakdown_fields.data_validation_col");
        $this->dataValMap = config("breakdown_fields.data_val_map");
        $this->recordId = $recordId;

        if ($recordId) {
            // Ambil modul berdasarkan ID dari URL
            $partComponent = PartComponent::find($recordId);

            if ($partComponent && $partComponent->part_component) {
                $decodedData = json_decode($partComponent->part_component, true);
                // Pastikan struktur data sesuai dengan yang diharapkan
                $this->partData = is_array($decodedData) ? $decodedData : [];
            } else {
                $this->partData = [];
            }
        }
    }

    public function save(Request $request)
    {
        $validated = $request->validate([
            'part_component' => 'required|array',
        ]);

        try {
            $partComponent = $validated['part_component'];

            $data = new PartComponent();
            $data->part_component = json_encode($partComponent);
            $data->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Data berhasil disimpan',
                'modul' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'part_component' => 'required|array',
            'columns' => 'required|array',
            'recordId' => 'required|integer'
        ]);

        try {
            $partComponent = $validated['part_component'];
            $recordId = $validated['recordId'];

            // Cari record modul yang akan diupdate
            $data = PartComponent::findOrFail($recordId);

            // Update data
            $data->part_component = json_encode($partComponent);
            $data->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Data berhasil diupdate',
                'part' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengupdate data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function render()
    {
        return view('livewire.part-component');
    }
}
