<?php

use App\Models\DescriptionUnit;
use App\Models\BoxCarcaseShape;
use App\Models\Finishing;
use App\Models\LayerPosition;
use App\Models\ClosingSystem;
use App\Models\NumberOfClosure;
use App\Models\TypeOfClosure;
use App\Models\Handle;
use App\Models\Accessories;
use App\Models\Lamp;
use App\Models\Plinth;

if (!function_exists('generateCabinetCode')) {
    function generateCabinetCode(
        $unitDescName,
        $boxShapeName,
        $finishing = null,
        $layerposition = null,
        $closingSystem = null,
        $numberOfClosures = null,
        $typeOfClosure = null,
        $handle = null,
        $accessories = null,
        $lamp = null,
        $plinth = null
    ) {
        $unitCode        = DescriptionUnit::where('name', $unitDescName)->value('code');
        $boxCode         = BoxCarcaseShape::where('name', $boxShapeName)->value('code');
        $fin             = Finishing::where('name', $finishing)->value('code');
        $layerpos        = LayerPosition::where('name', $layerposition)->value('code');
        $closeSys        = ClosingSystem::where('name', $closingSystem)->value('code');
        $numClosures     = NumberOfClosure::where('name', $numberOfClosures)->value('code');
        $typeClose       = TypeOfClosure::where('name', $typeOfClosure)->value('code');
        $handleCode      = Handle::where('name', $handle)->value('code');
        $accCode         = Accessories::where('name', $accessories)->value('code');
        $lampCode        = Lamp::where('name', $lamp)->value('code');
        $plinthCode      = Plinth::where('name', $plinth)->value('code');

        if ($unitCode && $boxCode) {
            return implode('', [
                $unitCode,
                $boxCode,
                $fin,
                $layerpos,
                $closeSys,
                $numClosures,
                $typeClose,
                $handleCode,
                $accCode,
                $lampCode,
                $plinthCode,
            ]);
        }

        return null;
    }
}
