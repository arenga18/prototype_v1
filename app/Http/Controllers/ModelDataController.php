<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Modul;
use App\Models\Project;
use Illuminate\Support\Facades\Log;

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
        // Validasi utama
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
            'nip' => 'required|string',

        ]);

        // Validasi terpisah untuk modul
        $modulValidated = $request->validate([
            'modul' => 'required|string'
        ]);
        $recordId = $request->validate([
            'recordId' => 'required|integer'
        ]);
        $modulToUpdate = $modulValidated['modul'];

        try {
            // Convert string numbers to integers
            foreach ($validated as $key => $value) {
                if ($value !== null && is_numeric($value) && $key !== 'code_cabinet' && $key !== 'nip') {
                    $validated[$key] = (int)$value;
                }
            }

            // Ambil data Project
            $project = Project::where('nip', $validated['nip'])
                ->where('id', $recordId)
                ->first();

            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'Project tidak ditemukan'
                ], 404);
            }

            // Pastikan modul_reference berupa array
            $modulReferences = $project->modul_reference;
            if (is_string($modulReferences)) {
                $modulReferences = json_decode($modulReferences, true) ?? [];
            }

            // Debugging: Log isi modulReferences dan modulToUpdate
            Log::info('modulReferences:', ['references' => $modulReferences]);
            Log::info('modulToUpdate:', ['modul' => $modulToUpdate]);

            // Update Modul utama
            $updatedModul = Modul::where('nip', $validated['nip'])
                ->where('code_cabinet', $modulToUpdate)
                ->update($validated);

            // Cek dan update modul_reference jika ada
            $updatedReference = false;
            if (in_array($modulToUpdate, $modulReferences, true)) {
                // Update modul_reference dengan code_cabinet baru
                $modulReferences = array_map(function ($item) use ($modulToUpdate, $validated) {
                    return $item === $modulToUpdate ? $validated['code_cabinet'] : $item;
                }, $modulReferences);

                // Update project
                $project->modul_reference = $modulReferences;
                $updatedReference = $project->save();

                // Debugging: Log hasil update
                // Log::info('Project after update:', ['project' => $project]);
                // Log::info('updated reference:', ['updatedReference' => $updatedReference]);
                // Log::info('Updated modul_reference:', ['new_references' => $modulReferences]);
            }

            return response()->json([
                'success' => $updatedModul > 0,
                'message' => $updatedModul > 0
                    ?  'Data modul berhasil diperbarui'
                    : 'Tidak ada data yang diubah',
                'updated_reference' => $updatedReference,
                'modul_references' => $modulReferences
            ]);
        } catch (\Exception $e) {
            Log::error('Error in updateModul: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function addModul(Request $request) {}
}
