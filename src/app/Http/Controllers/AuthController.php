<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Validator;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    /**
     * @OA\Post(
     *     path="/api/login",
     *     tags={"Authentication"},
     *     summary="Login",
     *     description="Login",
     *     @OA\RequestBody(
     *          @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="email",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="password",
     *                     type="string"
     *                 ),
     *                 example={"email": "matias@gmail.com", "password": "123456"}
     *             )
     *         )
     *      ),
     *      @OA\Response(response=201, description="Login",
     *      @OA\JsonContent(@OA\Examples(example="result", value={
     *           "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC8xMjcuMC4wLjE6ODAwMFwvYXBpXC9sb2dpbiIsImlhdCI6MTY4MjE4NjE4NiwiZXhwIjoxNjgyMTg5Nzg2LCJuYmYiOjE2ODIxODYxODYsImp0aSI6IkJvSXNNYVVBRnFMeWJuWjMiLCJzdWIiOjEsInBydiI6IjIzYmQ1Yzg5NDlmNjAwYWRiMzllNzAxYzQwMDg3MmRiN2E1OTc2ZjcifQ.3ZCgdeCVNSZljy4G3za_kTIRF018QQTAw-FHfywRZCc",
     *           "token_type": "bearer",
     *           "expires_in": 3600,
     *           "user": {
     *               "id": 1,
     *               "name": "matias",
     *               "email": "matias@gmail.com",
     *               "email_verified_at": null,
     *               "created_at": "2023-04-22T17:27:31.000000Z",
     *               "updated_at": "2023-04-22T17:27:31.000000Z"
     *           }
     *       }, summary="An result object.")) ),
     *      @OA\Response(response=400, description="Unprocessable Content",
     *      @OA\JsonContent(@OA\Examples(example="result", value={
     *           "error": "Verify email and password"
     *       }, summary="Required fields missing or wrong value(s)"))),
     *      @OA\Response(response=401, description="Unauthorized",
     *      @OA\JsonContent(@OA\Examples(example="result", value={
     *           "error": "Unauthorized"
     *       }, summary="An result object.")))
     * )
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Verify email and password'], 400);
        }

        if (!$token = auth()->attempt($validator->validated())) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->createNewToken($token);
    }

    /**
     * @OA\Post(
     *     path="/api/register",
     *     tags={"Authentication"},
     *     summary="Create user",
     *     description="Creation of a new user",
     *     @OA\RequestBody(
     *          @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="name",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="email",
     *                     type="string"
     *                 ),
     *                @OA\Property(
     *                     property="password",
     *                     type="string"
     *                 ),
     *                 example={"name": "Matias", "email": "matias@gmail.com", "password":"123456"}
     *             )
     *         )
     *      ),
     *      @OA\Response(response=201, description="Created",
     *      @OA\JsonContent(@OA\Examples(example="result", value={
     *          "message": "User successfully registered",
     *          "user": {
     *              "name": "Mario",
     *              "email": "mario@gmail.com",
     *              "updated_at": "2023-04-23T04:43:34.000000Z",
     *              "created_at": "2023-04-23T04:43:34.000000Z",
     *              "id": 2
     *          }
     *      }, summary="Message Success")) ),
     *      @OA\Response(response=422, description="Unprocessable Content",
     *      @OA\JsonContent(@OA\Examples(example="result", value={
     *           "field_name": "Required field"
     *       }, summary="Required Field register.")))
     * )
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::create(array_merge(
            $validator->validated(),
            ['password' => bcrypt($request->password)]
        ));

        return response()->json([
            'message' => 'User successfully registered',
            'user' => $user
        ], 201);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'User successfully signed out']);
    }

    /**
     * @OA\Get(
     *     path="/api/user",
     *     tags={"Authentication"},
     *     summary="Authenticated User Profile",
     *     description="User Profile",
     *     @OA\Response(response=200, description="Authenticated User Profile",
     *     @OA\JsonContent(@OA\Examples(example="result", value={
     *           "id": 1,
     *           "name": "matias",
     *           "email": "matias@gmail.com",
     *           "email_verified_at": null,
     *           "created_at": "2023-04-22T17:27:31.000000Z",
     *           "updated_at": "2023-04-22T17:27:31.000000Z"
     *       }, summary="An result object."))),
     *     @OA\Response(response=401, description="Unauthorized",
     *     @OA\JsonContent(@OA\Examples(example="result", value={
     *           "message": "Unauthenticated."
     *       }, summary="An result object."))),
     *     security={{"bearerAuth": {} }}
     * )
     */
    public function userProfile()
    {
        return response()->json(auth()->user());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function createNewToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'user' => auth()->user()
        ],201);
    }

    public function changePassWord(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'old_password' => 'required|string|min:6',
            'new_password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }
        $userId = auth()->user()->id;

        $user = User::where('id', $userId)->update(
            ['password' => bcrypt($request->new_password)]
        );

        return response()->json([
            'message' => 'User successfully changed password',
            'user' => $user,
        ], 201);
    }
}
