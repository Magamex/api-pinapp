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
        $this->middleware('auth:api', ['except' => ['kpiClients', 'getClient']]);
    }

    /**
     * @OA\Post(
     *     path="/api/clients",
     *     tags={"Client"},
     *     summary="Create Client",
     *     description="Creation of a new client",
     *     @OA\RequestBody(
     *          @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="name",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="lastname",
     *                     type="string"
     *                 ),
     *                @OA\Property(
     *                     property="age",
     *                     type="integer"
     *                 ),
     *                @OA\Property(
     *                     property="birth_date",
     *                     type="string"
     *                 ),
     *                 example={"name": "Matias", "lastname": "Angel", "age":31, "birth_date": "1990-01-01"}
     *             )
     *         )
     *      ),
     *      @OA\Response(response=201, description="Created",
     *      @OA\JsonContent(@OA\Examples(example="result", value={
     *           "message": "Client successfully created"
     *       }, summary="Message Success")) ),
     *      @OA\Response(response=422, description="Unprocessable Content",
     *      @OA\JsonContent(@OA\Examples(example="result", value={
     *           "field_name": "Required field"
     *       }, summary="An result object."))),
     *      @OA\Response(response=401, description="Unauthorized",
     *      @OA\JsonContent(@OA\Examples(example="result", value={
     *           "error": "Unauthorized"
     *       }, summary="Unauthorized"))),
     *       security={{"bearerAuth": {} }}
     * )
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

        return response()->json(['message' => 'Client successfully created'], 201);
    }

    /**
     * @OA\Delete(
     *     path="/api/clients/{id}",
     *     tags={"Client"},
     *     summary="Remove one client",
     *     description="Remove one client with ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID Client",
     *         required=true,
     *         example=3,
     *         @OA\Schema(
     *              type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Remove client",
     *         @OA\JsonContent(
     *             @OA\Examples(
     *                 example="result",
     *                 value={
     *                     "message": "Client successfully removed"
     *                 },
     *                 summary="Remove client"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Not ID",
     *         @OA\JsonContent(
     *             @OA\Examples(
     *                 example="result",
     *                 value={
     *                     "message": "Please provide a valid client ID"
     *                 },
     *                 summary="message error"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not Found",
     *         @OA\JsonContent(
     *             @OA\Examples(
     *                 example="result",
     *                 value={
     *                     "message": "The client does not exist"
     *                 },
     *                 summary="message error"
     *             )
     *         )),
     *     security={{"bearerAuth": {} }}
     * )
     */
    public function removeClient($id=null)
    {
        if (!$id) {
            return response()->json(['message' => 'Please provide a valid client ID'], 400);
        }

        $client = Client::find($id);

        if (!$client)
        {
            return response()->json(['message' => 'The client does not exist'], 404);
        }

        $client->delete();

        return response()->json(['message' => 'Client successfully removed'], 200);
    }



    /**
     * @OA\Get(
     *     path="/api/kpi-clients",
     *     tags={"Client"},
     *     summary="Get KPI Clients",
     *     description="Get KPI all Clients",
     *     @OA\Response(
     *         response=200,
     *         description="Get KPI",
     *         @OA\JsonContent(
     *             @OA\Examples(
     *                 example="result",
     *                 value={
     *                      "average_age": 50.33,
     *                      "standard_deviatio": 35.41
     *                 },
     *                 summary="KPI Clients"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not Found Clients",
     *         @OA\JsonContent(
     *             @OA\Examples(
     *                 example="result",
     *                 value={
     *                      "message": "Clients does not exist"
     *                 },
     *                 summary="Not Found Clients"
     *             )
     *         )
     *     )
     * )
     */
    public function kpiClients()
    {
        $clients = Client::all();

        if($clients->isEmpty()) {
            return response()->json(['message' => 'No clients registered'], 404);
        }

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
     * @OA\Get(
     *     path="/api/clients",
     *     tags={"Client"},
     *     summary="Get Clients",
     *     description="Get Clients With Death Date",
     *     @OA\Response(
     *         response=200,
     *         description="Get Clients",
     *         @OA\JsonContent(
     *             @OA\Examples(
     *                 example="result",
     *                 value={
     *                     {
     *                         "id": 1,
     *                         "name": "Matias",
     *                         "lastname": "Perez",
     *                         "age": 31,
     *                         "birth_date": "1991-12-12",
     *                         "death_date": "2031-04-02"
     *                     },
     *                     {
     *                         "id": 2,
     *                         "name": "Martin",
     *                         "lastname": "Perez",
     *                         "age": 20,
     *                         "birth_date": "1997-12-12",
     *                         "death_date": "2033-09-11"
     *                     }
     *                 },
     *                 summary="All Clients"
     *             )
     *         )
     *     ),
     *      @OA\Response(
     *         response=404,
     *         description="Clients Not Found",
     *         @OA\JsonContent(
     *             @OA\Examples(
     *                 example="result",
     *                 value={
     *                     "message": "No clients registered"
     *                 },
     *                 summary="Empty Clients"
     *             )
     *         )
     *     )
     * )
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

            $client->death_date = $this->calculateDeath($client);

            return response()->json([
                'id' => $client->id,
                'name' => $client->name,
                'lastname' => $client->lastname,
                'age' => $client->age,
                'birth_date' => $client->birth_date,
                'death_date' => $this->calculateDeath($client),
            ],200);
        }

        $clients = Client::all();

        if($clients->isEmpty()) {
            return response()->json(['message' => 'No clients registered'], 404);
        }

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
    /**
     * @OA\Get(
     *     path="/api/clients/{id}",
     *     tags={"Client"},
     *     summary="Get one client",
     *     description="Get One client with death date",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID Client",
     *         required=false,
     *         example=1,
     *         @OA\Schema(
     *              type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Get Client",
     *         @OA\JsonContent(
     *             @OA\Examples(
     *                 example="result",
     *                 value={
     *                     {
     *                         "id": 1,
     *                         "name": "Matias",
     *                         "lastname": "Perez",
     *                         "age": 31,
     *                         "birth_date": "1991-12-12",
     *                         "death_date": "2031-04-02"
     *                     },
     *                 },
     *                 summary="One Client"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not Found",
     *         @OA\JsonContent(
     *             @OA\Examples(
     *                 example="result",
     *                 value={
     *                     "message": "The client does not exist"
     *                 },
     *                 summary="message error"
     *             )
     *         ))
     * )
     */
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
