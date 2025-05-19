<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Google\Client as Google_Client;
use Illuminate\Support\Str;
use App\Models\User;

class AuthController extends Controller
{
  public function login(Request $request)
  {
    $request->validate([
      'id_token' => 'required|string'
    ]);
    $client = new Google_Client(['client_id' => $request->site->provider_client_id]);
    $payload = $client->verifyIdToken($request->id_token);
    if ($payload) {
      $user = User::where('email', $payload['email'])->first();
      if (!$user) {
        $user = User::create([
          'name' => $payload['name'],
          'email' => $payload['email'],
          'email_verified_at' => $payload['email_verified'] ? now() : null,
          'password' => bcrypt(Str::random(16)),
          'provider' => 'google',
          'provider_id' => $payload['sub'],
          'avatar' => $payload['picture'],
        ]);
      } else {
        $user->update([
          'name' => $payload['name'],
          'email_verified_at' => $payload['email_verified'] ? now() : null,
          'avatar' => $payload['picture'],
        ]);
      }
      $user->enrollments()->firstOrCreate(
        [
          'site_id' => $request->site->id,
          'user_id' => $user->id,
        ],
      );
      $token = $user->createToken('auth_token');

      return response()->json([
        'user' => $user,
        'token' => $token,
        'enrollment' => $user->enrollments()->where('site_id', $request->site->id)->first(),
      ]);
    }
    return response()->json(['message' => 'Invalid ID token'], 401);
  }
  public function logout(Request $request)
  {
    $request->user()->currentAccessToken()->delete();
    return response()->json(['message' => 'Logged out successfully'], 200);
  }
  public function me(Request $request)
  {
    $user = $request->user()->load(['enrollments' => function($query) use ($request) {
      $query->with('site');
    }]);
    return response()->json($user, 200);
  }
}
