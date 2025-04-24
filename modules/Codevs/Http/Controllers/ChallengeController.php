<?php

namespace Modules\Codevs\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Codevs\Models\Challenge;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Gate;

use Illuminate\Http\Request;

class ChallengeController extends Controller
{
  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    $challenges = Challenge::where('site_id', $request->site->id)
      ->with(['user'])
      ->orderBy('created_at', 'desc')
      ->paginate(15);
    $challenges->data = $challenges->makeHidden(['content', 'scaffold']);
    return response()->json($challenges);
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request)
  {
    $request->validate([
      'name' => 'required|string|max:255',
      'description' => 'required|string',
      'content' => 'required|string',
      'scaffold' => 'required|string',
      'funcName' => 'required|string|max:100',
      'level' => 'required|integer|min:0|max:5',
      'tests' => 'required|array|min:5',
      'tests.*.input' => 'required|string',
      'tests.*.output' => 'required|string',
    ]);

    $challenge = new Challenge();
    $challenge->site_id = $request->site->id;
    $challenge->user_id = Auth::user()->id;
    $challenge->slug = Str::slug($request->name).'-' . Str::random(4);
    $challenge->name = $request->name;
    $challenge->description = $request->description;
    $challenge->content = $request->content;
    $challenge->scaffold = $request->scaffold;
    $challenge->funcName = $request->funcName;
    $challenge->level = $request->level;

    $challenge->save();

    // Attach tests to the challenge
    foreach ($request->tests as $test) {
      $challenge->tests()->create([
        'input' => $test['input'],
        'output' => $test['output'],
      ]);
    }
    $challenge->load(['user', 'site']);

    return response()->json($challenge);
  }

  /**
   * Display the specified resource.
   */
  public function show(string $slug)
  {
    $challenge = Challenge::where('site_id', request()->site->id)
      ->where('slug', $slug)
      ->with(['user'])
      ->firstOrFail();

    return response()->json($challenge);
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, string $slug)
  {
    $challenge = Challenge::where('site_id', request()->site->id)->where('slug', $slug)->firstOrFail();
    if(Gate::denies('manage-challenge', $challenge)) { return response()->json(['message' => 'Unauthorized'], 403); }
    $request->validate([
      'name' => 'required|string|max:255',
      'description' => 'required|string',
      'content' => 'required|string',
      'scaffold' => 'required|string',
      'funcName' => 'required|string|max:100',
      'level' => 'required|integer|min:0|max:5',
    ]);
    $challenge->name = $request->name;
    $challenge->description = $request->description;
    $challenge->content = $request->content;
    $challenge->scaffold = $request->scaffold;
    $challenge->funcName = $request->funcName;
    $challenge->level = $request->level;
    $challenge->save();
    return response()->json($challenge);
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Request $request, string $slug)
  {
    $challenge = Challenge::where('site_id', request()->site->id)->where('slug', $slug)->firstOrFail();
    if(Gate::denies('manage-challenge', $challenge)) { return response()->json(['message' => 'Unauthorized'], 403); }

    $challenge->delete();
    return response()->json($challenge);
  }
}
