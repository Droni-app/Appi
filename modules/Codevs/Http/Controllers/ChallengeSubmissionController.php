<?php

namespace Modules\Codevs\Http\Controllers;
use App\Http\Controllers\Controller;
use Modules\Codevs\Models\Submission;
use Modules\Codevs\Models\Challenge;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

use Illuminate\Http\Request;

class ChallengeSubmissionController extends Controller
{
  /**
   * Display a listing of the resource.
   */
  public function index(Request $request, string $challengeSlug)
  {
    $challenge = Challenge::where('site_id', request()->site->id)->where('slug', $challengeSlug)->firstOrFail();
    $submissions = Submission::where('challenge_id', $challenge->id)
      ->with(['user'])
      ->orderBy('created_at', 'desc')
      ->paginate(15);
    return response()->json($submissions);
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request, string $challengeSlug)
  {
    $challenge = Challenge::where('site_id', request()->site->id)->where('slug', $challengeSlug)->firstOrFail();
    $request->validate([
      'code' => 'required|string',
      'complete' => 'required|boolean',
      'complete_time' => 'required|numeric',
    ]);

    $submission = new Submission();
    $submission->challenge_id = $challenge->id;
    $submission->user_id = Auth::user()->id;
    $submission->code = $request->code;
    $submission->complete = $request->complete;
    $submission->complete_time = $request->complete_time;
    $submission->rank = 0;
    $submission->votes = 0;

    $submission->save();
    return response()->json($submission);
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Request $request, string $challengeSlug, int $id)
  {
    $challenge = Challenge::where('site_id', $request->site->id)->where('slug', $challengeSlug)->firstOrFail();
    $submission = Submission::where('challenge_id', $challenge->id)->where('id', $id)->firstOrFail();

    if(Gate::denies('manage-submission', $submission)) { return response()->json(['message' => 'Unauthorized'], 403); }

    $submission->delete();

    return response()->json(['message' => 'Submission deleted successfully']);
  }
}
