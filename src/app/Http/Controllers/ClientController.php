<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use Illuminate\Support\Facades\Auth;
use Validator;

class ClientController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['kpiclients', 'listclients']]);
    }

    /**
     * Crea un nuevo cliente.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function createclient(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'lastname' => 'required|string',
            'age' => 'required|integer',
            'birth_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if (!$token = auth()->attempt($validator->validated())) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $request->validate([
            'name' => 'required|string',
            'lastname' => 'required|string',
            'age' => 'required|integer',
            'birth_date' => 'required|date',
        ]);

        $client = Client::create($request->all());

        return response()->json(['message' => 'Cliente creado exitosamente'], 201);
    }

    /**
     * Obtiene el promedio de edad y la desviación estándar de las edades de todos los clientes.
     *
     * @return \Illuminate\Http\Response
     */
    public function kpiclients()
    {
        $clientes = Client::all();

        $total_edades = 0;
        foreach ($clientes as $cliente) {
            $total_edades += $cliente->age;
        }

        $promedio_edad = $total_edades / count($clientes);

        $varianza = 0;
        foreach ($clientes as $cliente) {
            $varianza += pow($cliente->age - $promedio_edad, 2);
        }
        $varianza = $varianza / count($clientes);

        $desviacion_estandar = sqrt($varianza);

        return response()->json([
            'promedio_edad' => $promedio_edad,
            'desviacion_estandar' => $desviacion_estandar,
        ]);
    }

    /**
     * Obtiene una lista de todos los clientes con sus datos y fecha probable de muerte.
     *
     * @return \Illuminate\Http\Response
     */
    public function listclients()
    {
        $clientes = Client::all();

        $lista_clientes = [];
        foreach ($clientes as $cliente) {
            $vida_restante = 70 - $cliente->age;
            $fecha_probable_muerte = date('Y-m-d', strtotime("+$vida_restante years", strtotime($cliente->birth_date)));

            $lista_clientes[] = [
                'id' => $cliente->id,
                'name' => $cliente->name,
                'lastname' => $cliente->lastname,
                'age' => $cliente->age,
                'birth_date' => $cliente->birth_date,
                'death_date' => $fecha_probable_muerte,
            ];
        }

        return response()->json($lista_clientes);
    }
}
