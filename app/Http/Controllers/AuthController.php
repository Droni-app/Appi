<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{
  public function login(Request $request)
  {
    $request->validate([
      'siteId' => 'required|exists:sites,id'
    ]);
    return response()->json([
      'message' => 'Login successful'
    ]);
  }
}
