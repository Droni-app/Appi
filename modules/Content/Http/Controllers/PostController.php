<?php

namespace Modules\Content\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Content\Models\Category;
use Modules\Content\Models\Post;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    $filters = [
      'perPage' => $request->input('perPage', 20),
      'q' => $request->input('q', null),
      'category' => $request->input('category', null),
    ];
    $posts = Post::where('site_id', $request->site->id);

    if($filters['q']) {
      $posts = $posts->where(function($query) use ($filters) {
        $query->where('name', 'like', '%' . $filters['q'] . '%')
          ->orWhere('description', 'like', '%' . $filters['q'] . '%');
      });
    }
    if($filters['category']) {
      $category = Category::where('site_id', $request->site->id)->where('slug', $filters['category'])->firstOrFail();
      $posts = $posts->where('category_id', $category->id);
    }
    if(Gate::denies('manage-content', $request->site)) {
      $posts = $posts->where('active', true);
    }
    $posts = $posts->orderBy('created_at', 'desc')->with(['category', 'user', 'attrs'])->paginate($filters['perPage']);
    return response()->json($posts);
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request)
  {
    if(Gate::denies('manage-content', $request->site)) { return response()->json(['message' => 'Unauthorized'], 403); }
    $request->validate([
      'category' => 'required|exists:content_categories,slug',
      'name' => 'required|string|max:255|min:10',
      'description' => 'nullable|string',
      'picture' => 'nullable|string',
    ]);
    $slug = Post::where('site_id', $request->site->id)->where('slug', Str::slug($request->name))->first() ?
      Str::slug($request->name) . '-' . time() :
      Str::slug($request->name);

    $category = Category::where('site_id', $request->site->id)->where('slug', $request->category)->firstOrFail();
    $post = new Post();
    $post->site_id = $request->site->id;
    $post->user_id = Auth::user()->id;
    $post->category_id = $category->id;
    $post->slug = $slug;
    $post->name = $request->name;
    $post->description = $request->description;
    $post->picture = $request->picture;
    $post->content = $request->content;
    $post->format = $request->format ?? 'markdown';
    $post->active = $request->active ?? false;
    $post->save();

    return response()->json($post, 201);
  }

  /**
   * Display the specified resource.
   */
  public function show(request $request, string $slug)
  {
    $post = Post::where('site_id', $request->site->id)
      ->where('slug', $slug)
      ->with(['category', 'user', 'attrs'])
      ->firstOrFail();
    if(Gate::denies('manage-content', $request->site) && !$post->active) { return response()->json(['message' => 'Unauthorized'], 403); }
    return response()->json($post);
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, string $slug)
  {
    if(Gate::denies('manage-content', $request->site)) { return response()->json(['message' => 'Unauthorized'], 403); }
    $post = Post::where('site_id', $request->site->id)->where('slug', $slug)->firstOrFail();
    $request->validate([
      'category' => 'required|exists:content_categories,slug',
      'name' => 'required|string|max:255|min:10',
      'description' => 'nullable|string',
      'picture' => 'nullable|string',
    ]);
    $category = Category::where('site_id', $request->site->id)->where('slug', $request->category)->firstOrFail();
    $post->category_id = $category->id;
    $post->name = $request->name;
    $post->description = $request->description;
    $post->picture = $request->picture;
    $post->content = $request->content;
    $post->format = $request->format ?? 'markdown';
    $post->active = $request->active ?? false;

    $post->save();

    return response()->json($post);
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Request $request, string $slug)
  {
    if(Gate::denies('manage-content', $request->site)) { return response()->json(['message' => 'Unauthorized'], 403); }
    $post = Post::where('site_id', $request->site->id)->where('slug', $slug)->firstOrFail();
    $post->delete();
    return response()->json($post);
  }
}
