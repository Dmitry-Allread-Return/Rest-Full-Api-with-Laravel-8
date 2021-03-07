<?php

namespace App\Http\Controllers;

use App\Models\Airports;
use Illuminate\Http\Request;
use Validator;

class AirportsController extends Controller
{
    public function airport(Request $request) {
        $airports = Airports::query();
        // Валидация
        $rules = ['query' => 'required'];
        $validator = Validator::make($request->all(), $rules);
        if( $validator->fails() ) {
            return response()->json([
                'error' => [
                    'code' => 422,
                    'message' => 'Validation error',
                    'errors' => $validator->errors(),
                ]
                ], 422);
        }
        // Поиск запросов по query
        if ($request->get('query')) {
            $airports = Airports::where('city', 'like', '%'.$request->get('query').'%')->orWhere('name', 'like', '%'.$request->get('query').'%')
            ->orWhere('iata', 'like', '%'.$request->get('query').'%');
            
        }
        // Если ничего не найдено
        if (!$airports) {
            return response()->json([
                'data' => [
                    'items' => []
                ]
            ], 200);
        }
        // Ответ
        return response()->json([
            'data' => [
                'items' => $airports->get(['name', 'iata'])
            ]
        ], 200);
    }
}
