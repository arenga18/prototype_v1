<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Modul;
use Illuminate\Support\Facades\Validator;

class ModelDataController extends Controller
{
    // Mapping model names to their classes
    private $modelClasses = [
        'descriptionunit' => \App\Models\DescriptionUnit::class,
        'boxcarcaseshape' => \App\Models\BoxCarcaseShape::class,
        'finishing' => \App\Models\Finishing::class,
        'layerposition' => \App\Models\LayerPosition::class,
        'boxcarcasecontent' => \App\Models\BoxCarcaseContent::class,
        'closingsystem' => \App\Models\ClosingSystem::class,
        'numberofclosure' => \App\Models\NumberOfClosure::class,
        'typeofclosure' => \App\Models\TypeOfClosure::class,
        'handle' => \App\Models\Handle::class,
        'accessories' => \App\Models\Accessories::class,
        'lamp' => \App\Models\Lamp::class,
        'plinth' => \App\Models\Plinth::class,
    ];

    public function getModelData($model)
    {
        // Normalize model name
        $modelKey = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $model));

        if (!array_key_exists($modelKey, $this->modelClasses)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Model not found'
            ], 404);
        }

        try {
            $data = $this->modelClasses[$modelKey]::select('id', 'name', 'code')->get();

            return response()->json([
                'status' => 'success',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getModulByCabinet(Request $request)
    {
        $codeCabinet = $request->query('code_cabinet');
        $nip = $request->query('nip');

        if (!$codeCabinet) {
            return response()->json([
                'status' => 'error',
                'message' => 'code_cabinet is required'
            ], 400);
        }

        $moduls = Modul::where('code_cabinet', $codeCabinet)
            ->where('nip', $nip)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $moduls
        ]);
    }

    public function updateModul(Request $request)
    {
        $validated = $request->validate([
            'code_cabinet' => 'required|string',
            'description_unit' => 'nullable|string',
            'box_carcase_shape' => 'nullable|string',
            'finishing' => 'nullable|string',
            'layer_position' => 'nullable|string',
            'box_carcase_content' => 'nullable|string',
            'closing_system' => 'nullable|string',
            'number_of_closures' => 'nullable|string',
            'type_of_closure' => 'nullable|string',
            'handle' => 'nullable|string',
            'acc' => 'nullable|string',
            'lamp' => 'nullable|string',
            'plinth' => 'nullable|string',
            'nip' => 'required|string'
        ]);

        try {
            // Convert string numbers to integers
            foreach ($validated as $key => $value) {
                if ($value !== null && is_numeric($value) && $key !== 'code_cabinet' && $key !== 'nip') {
                    $validated[$key] = (int)$value;
                }
            }

            $updated = Modul::where('nip', $validated['nip'])
                ->where('code_cabinet', $request->validate(['modul' => 'required|string']))
                ->update($validated);

            return response()->json([
                'success' => $updated > 0,,
                'message' => $updated > 0 ? 'Data berhasil diperbarui' : 'Tidak ada data yang diubah'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
