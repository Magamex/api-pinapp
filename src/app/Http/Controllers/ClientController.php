<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use Illuminate\Support\Facades\Auth;
use Validator;
use DateTime;
use DateInterval;

class ClientController extends Controller
{
    /**
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['kpiclients', 'getClient']]);
    }

    /**
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function createClient(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'lastname' => 'required|string',
            'age' => 'required|integer|between:1,100',
            'birth_date' => 'required|date',
        ]);

        if ($validator->fails())
        {
            return response()->json($validator->errors(), 422);
        }

        $client = Client::create($validator->validated());

        return response()->json(['message' => 'Client successfully created',$client], 201);
    }

    /**
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function removeClient($id)
    {
        $client = Client::find($id);

        if (!$client)
        {
            return response()->json(['message' => 'The client does not exist'], 404);
        }

        $client->delete();

        return response()->json(['message' => 'Client successfully removed'], 200);
    }



    /**
     *
     * @return \Illuminate\Http\Response
     */
    public function kpiClients()
    {
        $clients = Client::all();

        $totalAges = 0;
        foreach ($clients as $client)
        {
            $totalAges += $client->age;
        }

        $avgAge = $totalAges / count($clients);

        $vari = 0;
        foreach ($clients as $client)
        {
            $vari += pow($client->age - $avgAge, 2);
        }
        $vari = $vari / count($clients);

        $standDev= sqrt($vari);

        return response()->json([
            'average_age' => round($avgAge,2),
            'standard_deviatio' => round($standDev,2),
        ]);
    }

    /**
     *
     * @return \Illuminate\Http\Response
     */
    public function getClient($id = null)
    {

        if($id)
        {
            $client = Client::find($id);

            if(!$client)
            {
                return response()->json(['message' => 'The client does not exist'], 404);
            }

            return response()->json(array_merge($client), ['death_date' => $this->calculateDeath($client)]);
        }

        $clients = Client::all();

        $listClients = [];
        foreach ($clients as $client)
        {
            $listClients[] = [
                'id' => $client->id,
                'name' => $client->name,
                'lastname' => $client->lastname,
                'age' => $client->age,
                'birth_date' => $client->birth_date,
                'death_date' => $this->calculateDeath($client),
            ];
        }

        return response()->json($listClients);
    }

    private function calculateDeath($client)
    {
        $tasaMortal = 0.01;

        $age = $client->age;
        $birthDate = new DateTime($client->birth_date);
        $deathDate = clone $birthDate;
        $deathDate->add(new DateInterval('P'.$age.'Y'));

        for ($i = $age; $i < 120; $i++) {
            $prob = 1 - $tasaMortal;
            for ($j = 1; $j <= $i - $age; $j++) {
                $prob *= (1 - $tasaMortal);
            }
            if (rand(0, 9999) < $prob * 10000) {
                $deathDate->add(new DateInterval('P1Y'));
            } else {
                break;
            }
        }

        $deathDate->setDate($deathDate->format('Y'), rand(1, 12), rand(1, 28));

        return $deathDate->format('Y-m-d');
    }
}
