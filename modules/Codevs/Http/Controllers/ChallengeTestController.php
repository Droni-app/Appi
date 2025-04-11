<?php

namespace Modules\Codevs\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Codevs\Models\Test;
use Modules\Codevs\Models\Challenge;
use Illuminate\Support\Facades\Gate;

use Illuminate\Http\Request;

class ChallengeTestController extends Controller
{
  /**
   * Display a listing of the resource.
   */
  public function index(Request $request, string $challengeSlug)
  {
    $challenge = Challenge::where('site_id', request()->site->id)->where('slug', $challengeSlug)->firstOrFail();
    $tests = Test::where('challenge_id', $challenge->id)->get();
    return response()->json($tests);
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request, string $challengeSlug)
  {
    $challenge = Challenge::where('site_id', request()->site->id)->where('slug', $challengeSlug)->firstOrFail();
    if(Gate::denies('manage-challenge', $challenge)) { return response()->json(['message' => 'Unauthorized'], 403); }
    $request->validate([
      'input' => 'required|string',
      'output' => 'required|string'
    ]);

    $test = new Test();
    $test->challenge_id = $challenge->id;
    $test->input = $request->input;
    $test->output = $request->output;

    $test->save();
    return response()->json($test);
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Request $request, string $challengeSlug, int $id)
  {
    $challenge = Challenge::where('site_id', request()->site->id)->where('slug', $challengeSlug)->firstOrFail();
    if(Gate::denies('manage-challenge', $challenge)) { return response()->json(['message' => 'Unauthorized'], 403); }
    $test = Test::where('challenge_id', $challenge->id)->where('id', $id)->firstOrFail();
    $test->delete();
    return response()->json($test);
  }
}
