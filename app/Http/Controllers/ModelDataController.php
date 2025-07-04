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
            $data = $this->modelClasses[$modelKey]::select('id', 'name')->get();

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

        if (!$codeCabinet) {
            return response()->json([
                'status' => 'error',
                'message' => 'code_cabinet is required'
            ], 400);
        }

        $moduls = Modul::where('code_cabinet', $codeCabinet)->get();

        return response()->json([
            'status' => 'success',
            'data' => $moduls
        ]);
    }
}
