<?php

namespace App\Http\Controllers;

use App\Models\Main;
use Illuminate\Http\Request;
use Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class MainController extends Controller
{
    // Регистрация
    public function register(Request $request) {
        $rules = [
        'first_name' => 'required|string',
        'last_name' => 'required|string',
        'phone' => 'required|unique:users|string',
        'document_number' => 'required|min:10|max:10|string',
        'password' => 'required|string',
        ];

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

        $create = Main::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'phone' => $request->phone,
            'document_number' => $request->document_number,
            'password' => Hash::make($request->password),
            // 'api_token' => Hash::make($request->password),
            'api_token' => uniqid().uniqid().uniqid(),
        ]);

        return response()->json($create, 204);
    }

    // Аутентификация
    public function login(Request $request)
    {
        $rules = [
            'phone' => 'required|string',
            'password' => 'required|string',
            ];
    
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

        // Проверка и генерация токена
        $user = Main::where('phone', $request->phone)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response([
                'error' => [
                    'code' => 401,
                    'message' => 'Unauthorized',
                    'errors' => ['phone' => ['phone or password incorrect']]
                ]
            ], 401);
        }

        $token = $user->api_token;
        $response = [
            'data' => ['token' => $token]
        ];

        return response($response, 200);
    }

    // // Получение списка аэропортов
    // public function airport(Request $request) {
    //     $query = $request->query;

    //     $results = DB::select('select * from airports where city = :query or name = :query or iata = :query', ['query' => $query]);

    //     return response()->json($results, 200);
    // }
}
