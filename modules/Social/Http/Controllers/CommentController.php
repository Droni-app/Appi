<?php

namespace Modules\Social\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Social\Models\Comment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CommentController extends Controller
{
  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    $commentables = [
      'content_post' => 'Modules\Content\Models\Post',
      'codevs_challenge' => 'Modules\CodeVs\Models\Challenge',
    ];
    $request->validate([
      'commentable_id' => 'integer',
      'commentable' => 'in:' . implode(',', array_keys($commentables)),
    ]);
    $filters = [
      'perPage' => $request->input('perPage', 10),
    ];
    $comments = Comment::with(['user', 'parent', 'children' => function($query) use ($request) {
      if(Gate::denies('manage-social', $request->site)) {
        $query->whereNotNull('approved_at')->with('user');
      }
    }])
      ->where('site_id', $request->site->id)
      ->orderBy('created_at', 'desc');

    // if commentable_id is provided, filter by it
    if($request->commentable_id && $request->commentable) {
      $commentable = $commentables[$request->commentable]::where('site_id', $request->site->id)->findOrFail($request->commentable_id);
      $comments = $comments->where('commentable_id', $commentable->id)
        ->where('commentable_type', $commentable->getMorphClass())
        ->where('parent_id', null);
    }

    if(Gate::denies('manage-social', $request->site)) {
      $comments = $comments->whereNotNull('approved_at');
    }

    $comments = $comments->paginate($filters['perPage']);
    return response()->json($comments);
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request)
  {
    $commentables = [
      'content_post' => 'Modules\Content\Models\Post',
      'codevs_challenge' => 'Modules\CodeVs\Models\Challenge',
    ];
    $request->validate([
      'commentable_id' => 'required|integer',
      'commentable' => 'required|in:' . implode(',', array_keys($commentables)),
      'content' => 'required|string|max:1000',
      'parent_id' => 'nullable|integer',
    ]);
    /* find comementable */
    $commentable = $commentables[$request->commentable]::where('site_id', $request->site->id)->findOrFail($request->commentable_id);

    $parent = Comment::where('site_id', $request->site->id)->whereNull('parent_id')->find($request->parent_id);

    $comment = new Comment();
    $comment->site_id = $request->site->id;
    $comment->user_id = Auth::user()->id;
    $comment->parent_id = $parent ? $parent->id : null;
    $comment->commentable_id = $commentable->id;
    $comment->commentable_type = $commentable->getMorphClass();
    $comment->content = $request->content;
    $comment->save();

    return response()->json($comment, 201);

  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, string $id)
  {
    if(Gate::denies('manage-social', $request->site)) { return response()->json(['message' => 'Unauthorized'], 403); }
    $request->validate([
      'content' => 'string|max:1000',
      'approved' => 'nullable|boolean',
    ]);
    $comment = Comment::where('site_id', $request->site->id)->findOrFail($id);

    $comment->content = $request->content;
    $comment->approved_at = $request->approved ? now() : null;
    $comment->save();
    return response()->json($comment);
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Request $request, string $id)
  {
    $comment = Comment::where('site_id', $request->site->id)->findOrFail($id);
    if(Gate::denies('manage-social', $request->site) && $comment->user_id !== Auth::user()->id) {
      return response()->json(['message' => 'Unauthorized'], 403);
    }
    $comment->children()->delete();
    $comment->delete();
    return response()->json(['message' => 'Comment deleted successfully']);
  }
}
