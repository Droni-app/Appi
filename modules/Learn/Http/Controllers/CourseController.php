<?php

namespace Modules\Learn\Http\Controllers;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Modules\Learn\Models\Course;

class CourseController extends Controller
{
  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    $filters = [
      'perPage' => $request->input('perPage', 10),
      'q' => $request->input('q', null),
    ];
    $courses = Course::where('site_id', $request->site->id)
      ->orderBy('created_at', 'desc')
      ->paginate($filters['perPage']);

    return response()->json($courses);
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request)
  {
    if(Gate::denies('manage-learn', $request->site)) { return response()->json(['message' => 'Unauthorized'], 403); }

    $request->validate([
      'category' => 'nullable|string|max:100',
      'name' => 'required|string|max:255|min:5',
      'description' => 'required|string',
    ]);

    $course = new Course();
    $course->user_id = $request->user()->id;
    $course->site_id = $request->site->id;
    $course->category = $request->category;
    $course->slug = Str::slug($request->name, '-').'-' . Str::random(4);
    $course->name = $request->name;
    $course->icon = $request->icon;
    $course->picture = $request->picture;
    $course->video = $request->video;
    $course->description = $request->description;
    $course->open = $request->open ?? true;
    $course->save();

    return response()->json($course, 201);
  }

  /**
   * Display the specified resource.
   */
  public function show(string $slug)
  {
    $course = Course::where('site_id', request()->site->id)->where('slug', $slug)->firstOrFail();
    return response()->json($course);
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, string $slug)
  {
    if(Gate::denies('manage-learn', $request->site)) { return response()->json(['message' => 'Unauthorized'], 403); }

    $request->validate([
      'category' => 'nullable|string|max:100',
      'name' => 'required|string|max:255|min:5',
      'description' => 'nullable|string',
    ]);

    $course = Course::where('site_id', $request->site_id)->where('slug', $slug)->firstOrFail();
    $course->user_id = $request->user_id;
    $course->site_id = $request->site_id;
    $course->category = $request->category;
    $course->name = $request->name;
    $course->icon = $request->icon;
    $course->picture = $request->picture;
    $course->video = $request->video;
    $course->description = $request->description;
    $course->open = $request->open ?? true;
    $course->save();
    return response()->json($course);
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(string $slug)
  {
    if(Gate::denies('manage-learn', request()->site)) { return response()->json(['message' => 'Unauthorized'], 403); }

    $course = Course::where('site_id', request()->site->id)->where('slug', $slug)->firstOrFail();
    $course->delete();
    return response()->json($course);
  }
}
