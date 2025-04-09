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
      }
      $token = $user->createToken('auth_token');
      return response()->json(['user' => $user, 'token' => $token->plainTextToken], 200);
    }

    return response()->json(['message' => 'Invalid ID token'], 401);
  }
}
