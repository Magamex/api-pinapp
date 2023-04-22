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
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */

    /**
     * @OA\POST(
     *     path="/api/login",
     *     tags={"Authentication"},
     *     summary="Login",
     *     description="Login",
     *     @OA\Parameter(
     *         name="email",
     *         in="query",
     *         description="Correo electronico",
     *         required=true,
     *         example="matias@gmail.com",
     *         @OA\Schema(
     *              type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="password",
     *         in="query",
     *         description="Contrasenia",
     *         required=true,
     *         example="123456",
     *         @OA\Schema(
     *              type="string"
     *         )
     *     ),
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
     *      @OA\Response(response=200, description="Login",
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
     *      @OA\Response(response=422, description="Unprocessable Content",
     *      @OA\JsonContent(@OA\Examples(example="result", value={
     *           "password": "The password must be at least 6 characters."
     *       }, summary="An result object."))),
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
            return response()->json($validator->errors(), 422);
        }

        if (!$token = auth()->attempt($validator->validated())) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->createNewToken($token);
    }

    /**
     * Register a User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
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
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->createNewToken(auth()->refresh());
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * @OA\GET(
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
     *     @OA\Response(response=400, description="Bad request"),
     *     @OA\Response(response=404, description="Resource Not Found"),
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
        ]);
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