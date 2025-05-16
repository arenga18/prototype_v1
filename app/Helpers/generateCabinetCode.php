<?php

use App\Models\DescriptionUnit;
use App\Models\BoxCarcaseShape;
use App\Models\Finishing;
use App\Models\LayerPosition;

if (!function_exists('generateCabinetCode')) {
    function generateCabinetCode($unitDescName, $boxShapeName, $finishing, $layerposition)
    {
        $unitCode = DescriptionUnit::where('name', $unitDescName)->value('code');
        $boxCode = BoxCarcaseShape::where('name', $boxShapeName)->value('code');
        $fin = Finishing::where('name', $finishing)->value('code');
        $layerpos = LayerPosition::where('name', $layerposition)->value('code');

        if ($unitCode && $boxCode) {
            return $unitCode . $boxCode . $fin . $layerpos;
        }

        return null;
    }
}
