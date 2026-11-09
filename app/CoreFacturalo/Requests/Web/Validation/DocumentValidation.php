<?php

namespace App\CoreFacturalo\Requests\Web\Validation;

use Illuminate\Validation\ValidationException;

class DocumentValidation
{
    public static function validation($inputs) {
        $series = Functions::findSeries($inputs);
        if (!$series) {
            throw ValidationException::withMessages([
                'series_id' => 'No existe una serie configurada para el establecimiento y tipo de comprobante seleccionado.',
            ]);
        }
        $inputs['series'] = $series->number;
        unset($inputs['series_id']);
        
        Functions::DNI($inputs);
        Functions::identityDocumentTypeInvoice($inputs);
        
        return $inputs;
    }
}