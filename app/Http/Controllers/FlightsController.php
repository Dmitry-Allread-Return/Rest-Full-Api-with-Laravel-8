<?php

namespace App\Http\Controllers;

use App\Models\flights;
use App\Models\Airports;
use Illuminate\Http\Request;
use Validator;


class FlightsController extends Controller
{
    public function flights(Request $request)
    {
        $rules = [
            'from' => 'required',
            'to' => 'required',
            'date1' => 'required|date_format:Y-m-d',
            'date2' => 'date_format:Y-m-d',
            'passengers' => 'min:1|max:8|required',
        ];
        $validator = Validator::make($request->all(), $rules);
        if($validator->fails()) {
            return response()->json([
                'error' => [
                    'code' => 422,
                    'message' => 'Validation error',
                    'errors' => $validator->errors(),
                ]
                ], 422);
        }

        // Извлечение id из таблицы airports
        $fromId = Airports::where('iata', $request->get('from'))->value('id');
        $toId = Airports::where('iata', $request->get('to'))->value('id');

        // Извлечение строки из таблицы airports
        $fromStr = Airports::where('id', $fromId);
        $toStr = Airports::where('id', $toId);

        // Все найденный рейсы туда
        $flights = flights::where('from_id', $fromId)
        ->where('to_id', $toId)->get(); // $flights->get()

        // Получение всех рейсов туда в переменную $resp
        for($i = 0; $i < count($flights); $i++) {
            $resp[$i] = [
                'flights_id' => $flights[$i]['id'],
                'flights_code' => $flights[$i]['flight_code'],
                'from' => [
                    'city' => $fromStr->value('city'),
                    'airport' => $fromStr->value('name'),
                    'iata' => $fromStr->value('iata'),
                    'date' => $request->get('date1'),
                    'time' => $flights[$i]['time_from'],
                ],
                'to' => [
                    'city' => $toStr->value('city'),
                    'airport' => $toStr->value('name'),
                    'iata' => $toStr->value('iata'),
                    'date' => $request->get('date2'),
                    'time' => $flights[$i]['time_to'],
                ],
                'cost' => $flights[$i]['cost'],                           
            ];                   
        }

        // Все найденный рейсы обратно
        $flights_back = flights::where('from_id', $toId)
        ->where('to_id', $fromId)->get();

        // Получение всех рейсов обратно в переменную $resp_back
        for($i = 0; $i < count($flights_back); $i++) {
            $resp_back[$i] = [
                'flights_id' => $flights_back[$i]['id'],
                'flights_code' => $flights_back[$i]['flight_code'],
                'from' => [
                    'city' => $toStr->value('city'),
                    'airport' => $toStr->value('name'),
                    'iata' => $toStr->value('iata'),
                    'date' => $request->get('date1'),
                    'time' => $flights_back[$i]['time_from'],
                ],
                'to' => [
                    'city' => $fromStr->value('city'),
                    'airport' => $fromStr->value('name'),
                    'iata' => $fromStr->value('iata'),
                    'date' => $request->get('date2'),
                    'time' => $flights_back[$i]['time_to'],
                ],
                'cost' => $flights_back[$i]['cost'],                           
            ];                   
        }
        // Ответ сервера
        if(!$request->date2) {   //Если НЕ указана дата возвращения
            return response()->json([
                'data' => [ 
                    'flights_to' => $resp,
                    'flights_back' => [],
                ]
            ], 200);
        } else {   //Если указана дата возвращения
            return response()->json([
                'data' => [ 
                    'flights_to' => $resp,
                    'flights_back' => $resp_back,
                ]
            ], 200);
        }
        
    }

}
