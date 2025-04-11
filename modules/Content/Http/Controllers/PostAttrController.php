<?php

namespace Modules\Content\Http\Controllers;
use Illuminate\Routing\Controller;
use Modules\Content\Models\Attr;
use Modules\Content\Models\Post;
use Illuminate\Support\Facades\Gate;

use Illuminate\Http\Request;

class PostAttrController extends Controller
{
  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request, string $postSlug)
  {
    if(Gate::denies('manage-content', $request->site)) { return response()->json(['message' => 'Unauthorized'], 403); }

    $post = Post::where('site_id', $request->site->id)->where('slug', $postSlug)->firstOrFail();
    $request->validate([
      'name' => 'required|string|max:255',
      'value' => 'required|string|max:255',
    ]);

    $attr = new Attr();
    $attr->post_id = $post->id;
    $attr->name = $request->name;
    $attr->type = $request->type ?? 'string';
    $attr->value = $request->value;
    $attr->save();

    return response()->json($attr, 201);
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Request $request, string $postSlug, string $id)
  {
    if(Gate::denies('manage-content', $request->site)) { return response()->json(['message' => 'Unauthorized'], 403); }
    $post = Post::where('site_id', $request->site->id)->where('slug', $postSlug)->firstOrFail();
    $attr = Attr::where('post_id', $post->id)->where('id', $id)->firstOrFail();
    $attr->delete();
    return response()->json($attr);
  }
}
