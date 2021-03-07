<?php

namespace App\Http\Controllers;

use App\Models\booking;
use App\Models\passengers;
use App\Models\Airports;
use Illuminate\Http\Request;
use Validator;
use App\Models\Main;

class BookingController extends Controller
{
    public function booking(Request $request)
    {
        $rules = [
            'flight_from' => 'array|required',
            'flight_from.id' => 'required',
            'flight_from.date' => 'required|date_format:Y-m-d',
            'flight_back',
            'flight_back.id' => 'required',
            'flight_back.date' => 'required|date_format:Y-m-d',
            'passengers' => 'array|required',
            'passengers.*.first_name' => 'required',
            'passengers.*.last_name' => 'required',
            'passengers.*.birth_date' => 'required|date_format:Y-m-d',
            'passengers.*.document_number' => 'required|min:10|max:10',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'error' => [
                    'code' => 422,
                    'message' => 'Validation error',
                    'errors' => $validator->errors(),
                ]
            ], 422);
        }
        // Генерация уникального кода бронирования
        $dada = substr(str_shuffle(implode("", range('A', 'Z'))), 0, 5);
        // Создание бронирования
        $note = booking::create([
            'flight_from' => $request->flight_from['id'],
            'flight_back' => $request->flight_back['id'],
            'date_from' => $request->flight_from['date'],
            'date_back' => $request->flight_back['date'],
            'code' => $dada,
        ]);
        // Отправка пассажиров в базу
        for($i = 0; $i < count($request->passengers); $i++) {
            $pas = passengers::create([
                'booking_id' => $note->id,
                'first_name' => $request->passengers[$i]['first_name'],
                'last_name' => $request->passengers[$i]['last_name'],
                'birth_date' => $request->passengers[$i]['birth_date'],
                'document_number' => $request->passengers[$i]['document_number'],
            ]);
        }

        // Получение уникального кода бронирования
        $code = booking::where('id', $note->id)->value('code');
        // return response()->json($request->all(), 200);
        return response()->json([
            "data" => [
                "code" => $code
            ]
        ], 201);
    }

    public function bookingInfo($code) {
        
        $res = booking::where('code', '=', $code)->with('fTo')->with('fBack')->with('passengers')->first();
        $cost = $res['fTo']['cost'] + $res['fBack']['cost'];
        $airTo1 = Airports::where('id', $res['fTo']['from_id'])->first();
        $airTo2 = Airports::where('id', $res['fTo']['to_id'])->first();

        for($i = 0; $i < count($res->passengers); $i++) {
            $pas[$i] = [
                'id' => $res->passengers[$i]['id'],
                'first_name' => $res->passengers[$i]['first_name'],
                'last_name' => $res->passengers[$i]['last_name'],
                'birth_date' => $res->passengers[$i]['birth_date'],
                'document_number' => $res->passengers[$i]['document_number'],
                'place_from' => $res->passengers[$i]['place_from'],
                'place_back' => $res->passengers[$i]['place_back'],
            ];
        }
        
        return response()->json([
            'data' => [
                'code' => $code,
                'cost' => $cost,
                'flights' => [
                    [
                        'flight_id' => $res['fTo']['id'],
                        'flight_code' => $res['fTo']['flight_code'],
                        'from' => [
                            'city' => $airTo1['city'],
                            'airport' => $airTo1['name'],
                            'iata' => $airTo1['iata'],
                            'date' => $res['date_from'],
                            'time' => $res['fTo']['time_from'],
                        ],
                        'to' => [
                            'city' => $airTo2['city'],
                            'airport' => $airTo2['name'],
                            'iata' => $airTo2['iata'],
                            'date' => $res['date_from'],
                            'time' => $res['fTo']['time_to'],
                        ],
                        'cost' => $res['fTo']['cost']
                    ],
                    [
                        'flight_id' => $res['fBack']['id'],
                        'flight_code' => $res['fBack']['flight_code'],
                        'from' => [
                            'city' => $airTo2['city'],
                            'airport' => $airTo2['name'],
                            'iata' => $airTo2['iata'],
                            'date' => $res['date_back'],
                            'time' => $res['fBack']['time_from'],
                        ],
                        'to' => [
                            'city' => $airTo1['city'],
                            'airport' => $airTo1['name'],
                            'iata' => $airTo1['iata'],
                            'date' => $res['date_back'],
                            'time' => $res['fBack']['time_to'],
                        ],
                        'cost' => $res['fBack']['cost']
                    ]
                ],
                'passengers' => $pas,

            ]
        ], 200);


        
    }



    public function mybrone(Request $request) {

        $token = $request->header('Authorization');
        $redToken = str_replace('Bearer ', '', $token);
        $getToken = Main::where('api_token', $redToken)->with('passenger')->first();
        if (!$getToken) {
            return response()->json([
                'error' => [
                    'code' => 401,
                    'message' => 'Unauthorized'
                ]
            ], 401);
            exit;
        }
        $booking_id = $getToken['passenger'][0]['booking_id'];
        $booking = booking::where('id', $booking_id)
        ->with('passengers')
        ->with('fTo')
        ->with('fBack')
        ->first();
        $cost = $booking['fTo']['cost'] + $booking['fBack']['cost'];

        // airports from and to (SVO,KZN...)
        $airports_from = Airports::where('id', $booking['fTo']['from_id'])->first();
        $airports_to = Airports::where('id', $booking['fTo']['to_id'])->first();


        for($i = 0; $i < count($booking->passengers); $i++) {
            $pas[$i] = [
                'id' => $booking->passengers[$i]['id'],
                'first_name' => $booking->passengers[$i]['first_name'],
                'last_name' => $booking->passengers[$i]['last_name'],
                'birth_date' => $booking->passengers[$i]['birth_date'],
                'document_number' => $booking->passengers[$i]['document_number'],
                'place_from' => $booking->passengers[$i]['place_from'],
                'place_back' => $booking->passengers[$i]['place_back'],
            ];
        }
        return response()->json([
            'data' => [
                'items' => [
                    [
                        'code' => $booking['code'],
                        'cost' => $cost,
                        'flihts' => [
                            [
                                'flight_id' => $booking['fTo']['id'],
                                'flight_code' => $booking['fTo']['flight_code'],
                                'from' => [
                                    'city' => $airports_from['city'],
                                    'airport' => $airports_from['name'],
                                    'iata' => $airports_from['iata'],
                                    'date' => $booking['date_from'],
                                    'time' => $booking['fTo']['time_from'],
                                ],
                                'to' => [
                                    'city' => $airports_to['city'],
                                    'airport' => $airports_to['name'],
                                    'iata' => $airports_to['iata'],
                                    'date' => $booking['date_from'],
                                    'time' => $booking['fTo']['time_to'],
                                ],
                                'cost' => $booking['fTo']['cost']
                            ],
                            [
                                'flight_id' => $booking['fBack']['id'],
                                'flight_code' => $booking['fBack']['flight_code'],
                                'from' => [
                                    'city' => $airports_to['city'],
                                    'airport' => $airports_to['name'],
                                    'iata' => $airports_to['iata'],
                                    'date' => $booking['date_back'],
                                    'time' => $booking['fBack']['time_from'],
                                ],
                                'to' => [
                                    'city' => $airports_from['city'],
                                    'airport' => $airports_from['name'],
                                    'iata' => $airports_from['iata'],
                                    'date' => $booking['date_back'],
                                    'time' => $booking['fBack']['time_to'],
                                ],
                                'cost' => $booking['fTo']['cost']
                            ]
                        ],
                        'passengers' => $pas
                    ]
                ]
            ]
        ], 200);
    }


    public function user(Request $request) {
        $token = $request->header('Authorization');
        $redToken = str_replace('Bearer ', '', $token);
        $getToken = Main::where('api_token', $redToken)->first();
        if (!$getToken) {
            return response()->json([
                'error' => [
                    'code' => 401,
                    'message' => 'Unauthorized'
                ]
            ], 401);
            exit;
        }
        return response()->json([
            'first_name' => $getToken['first_name'],
            'last_name' => $getToken['last_name'],
            'phone' => $getToken['phone'],
            'document_number' => $getToken['document_number'],
        ], 200);
    }
}
