<?php

namespace App\Modules\User\Controllers;

use App\Core\Application\Services\SubscriptionService;
use App\Http\Controllers\Controller;
use App\Modules\User\Requests\LoginRequest;
use App\Modules\User\Requests\RegisterRequest;
use App\Modules\User\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

/**
 * @group Authentication
 * 
 * Authentication endpoints for user registration, login, and profile management.
 */
class AuthController extends Controller
{
    public function __construct(
        private SubscriptionService $subscriptionService
    ) {}

    /**
     * Register a new user
     * 
     * Creates a new user account and automatically creates a trial subscription.
     * 
     * @bodyParam name string required User's full name. Example: John Doe
     * @bodyParam email string required User's email address. Example: john@example.com
     * @bodyParam password string required User's password (min 8 characters). Example: password123
     * 
     * @response 201 {
     *   "data": {
     *     "id": 1,
     *     "name": "John Doe",
     *     "email": "john@example.com",
     *     "created_at": "2023-01-01T00:00:00.000000Z"
     *   },
     *   "message": "User registered successfully"
     * }
     */
    public function register(RegisterRequest $request): Response
    {
        $user = \App\Models\User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Create trial subscription
        $this->subscriptionService->createTrial($user);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response([
            'data' => new UserResource($user),
            'token' => $token,
            'message' => 'User registered successfully',
        ], 201);
    }

    /**
     * Login user
     * 
     * Authenticates a user and returns an API token.
     * 
     * @bodyParam email string required User's email address. Example: john@example.com
     * @bodyParam password string required User's password. Example: password123
     * 
     * @response 200 {
     *   "data": {
     *     "id": 1,
     *     "name": "John Doe",
     *     "email": "john@example.com"
     *   },
     *   "token": "1|abc123...",
     *   "message": "Login successful"
     * }
     * @response 401 {
     *   "message": "Invalid credentials"
     * }
     */
    public function login(LoginRequest $request): Response
    {
        $user = \App\Models\User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response([
                'message' => 'Invalid credentials',
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response([
            'data' => new UserResource($user),
            'token' => $token,
            'message' => 'Login successful',
        ]);
    }

    /**
     * Logout user
     * 
     * Revokes the current API token.
     * 
     * @authenticated
     * @response 200 {
     *   "message": "Logout successful"
     * }
     */
    public function logout(Request $request): Response
    {
        $request->user()->currentAccessToken()->delete();

        return response([
            'message' => 'Logout successful',
        ]);
    }

    /**
     * Get current user
     * 
     * Returns the authenticated user's profile information.
     * 
     * @authenticated
     * @response 200 {
     *   "data": {
     *     "id": 1,
     *     "name": "John Doe",
     *     "email": "john@example.com",
     *     "created_at": "2023-01-01T00:00:00.000000Z"
     *   }
     * }
     */
    public function me(Request $request): Response
    {
        return response([
            'data' => new UserResource($request->user()),
        ]);
    }
}
