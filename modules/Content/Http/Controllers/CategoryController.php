<?php

namespace Modules\Content\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Content\Models\Category;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    $categories = Category::where('site_id', $request->site->id)->get();
    return response()->json($categories);
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request)
  {
    if(Gate::denies('manage-content', $request->site)) { return response()->json(['message' => 'Unauthorized'], 403); }
    $request->validate([
      'name' => 'required|string|max:255',
      'description' => 'nullable|string',
      'picture' => 'nullable|string',
    ]);
    $category = new Category();
    $category->site_id = $request->site->id;
    $category->slug = Str::slug($request->name).'-'.Str::random(10);
    $category->name = $request->name;
    $category->description = $request->description;
    $category->picture = $request->picture;
    $category->save();
    return response()->json($category, 201);

  }

  /**
   * Display the specified resource.
   */
  public function show(request $request, string $slug)
  {
    $category = Category::where('site_id', $request->site->id)
      ->where('slug', $slug)
      ->firstOrFail();
    return response()->json($category);
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, string $slug)
  {
    if(Gate::denies('manage-content', $request->site)) { return response()->json(['message' => 'Unauthorized'], 403); }
    $category = Category::where('site_id', $request->site->id)->where('slug', $slug)->firstOrFail();
    $request->validate([
      'name' => 'required|string|max:255',
      'description' => 'nullable|string',
      'picture' => 'nullable|string',
    ]);
    $category->name = $request->name;
    $category->description = $request->description;
    $category->picture = $request->picture;
    $category->save();

    return response()->json($category);
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Request $request, string $slug)
  {
    if(Gate::denies('manage-content', $request->site)) { return response()->json(['message' => 'Unauthorized'], 403); }
    $category = Category::where('site_id', $request->site->id)->where('slug', $slug)->firstOrFail();
    $category->delete();
    return response()->json($category);
  }
}
