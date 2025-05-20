<?php

namespace Modules\Learn\Http\Controllers;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Modules\Learn\Models\Course;
use Modules\Learn\Models\Lesson;

class CourseLessonController extends Controller
{
  /**
   * Display a listing of the resource.
   */
  public function index(Request $request, string $courseSlug)
  {
    $course = Course::where('site_id', $request->site->id)
      ->where('slug', $courseSlug)
      ->firstOrFail();

    $perPage = $request->input('perPage', 30);
    $lessons = $course->lessons()
      ->orderBy('position')
      ->paginate($perPage);

    return response()->json($lessons);
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request, string $courseSlug)
  {
    if (Gate::denies('manage-learn', $request->site)) {
      return response()->json(['message' => 'Unauthorized'], 403);
    }

    $request->validate([
      'position' => 'nullable|integer',
      'type' => 'required|string|max:50',
      'name' => 'required|string|max:255',
      'video' => 'nullable|string|max:500',
      'live' => 'nullable|string|max:500',
      'live_date' => 'nullable|date',
      'description' => 'nullable|string',
      'content' => 'nullable|string',
      'is_activity' => 'nullable|boolean',
      'limit_date' => 'nullable|date',
      'active' => 'nullable|boolean',
    ]);

    $course = Course::where('site_id', $request->site->id)
      ->where('slug', $courseSlug)
      ->firstOrFail();

    $lesson = new Lesson();
    $lesson->course_id = $course->id;
    $lesson->position = $request->input('position', 0);
    $lesson->type = $request->input('type');
    $lesson->name = $request->input('name');
    $lesson->slug = Str::slug($lesson->name, '-') . '-' . Str::random(4);
    $lesson->video = $request->input('video');
    $lesson->live = $request->input('live');
    $lesson->live_date = $request->input('live_date');
    $lesson->description = $request->input('description');
    $lesson->content = $request->input('content');
    $lesson->is_activity = $request->boolean('is_activity', false);
    $lesson->limit_date = $request->input('limit_date');
    $lesson->active = $request->boolean('active', true);
    $lesson->save();

    return response()->json($lesson, 201);
  }

  /**
   * Display the specified resource.
   */
  public function show(Request $request, string $courseSlug, string $slug)
  {
    $course = Course::where('site_id', $request->site->id)
      ->where('slug', $courseSlug)
      ->firstOrFail();

    $lesson = $course->lessons()
      ->where('slug', $slug)
      ->firstOrFail();

    return response()->json($lesson);
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, string $courseSlug, string $slug)
  {
    if (Gate::denies('manage-learn', $request->site)) {
      return response()->json(['message' => 'Unauthorized'], 403);
    }
    $request->validate([
      'position' => 'nullable|integer',
      'type' => 'required|string|max:50',
      'name' => 'required|string|max:255',
      'video' => 'nullable|string|max:500',
      'live' => 'nullable|string|max:500',
      'live_date' => 'nullable|date',
      'description' => 'nullable|string',
      'content' => 'nullable|string',
      'is_activity' => 'nullable|boolean',
      'limit_date' => 'nullable|date',
      'active' => 'nullable|boolean',
    ]);

    $course = Course::where('site_id', $request->site->id)
      ->where('slug', $courseSlug)
      ->firstOrFail();

    $lesson = $course->lessons()
      ->where('slug', $slug)
      ->firstOrFail();

    $lesson->position = $request->input('position', $lesson->position);
    $lesson->type = $request->input('type', $lesson->type);
    $lesson->name = $request->input('name', $lesson->name);
    $lesson->video = $request->input('video', $lesson->video);
    $lesson->live = $request->input('live', $lesson->live);
    $lesson->live_date = $request->input('live_date', $lesson->live_date);
    $lesson->description = $request->input('description', $lesson->description);
    $lesson->content = $request->input('content', $lesson->content);
    $lesson->is_activity = $request->boolean('is_activity', $lesson->is_activity);
    $lesson->limit_date = $request->input('limit_date', $lesson->limit_date);
    $lesson->active = $request->boolean('active', $lesson->active);
    $lesson->save();

    return response()->json($lesson);
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Request $request, string $courseSlug, string $slug)
  {
    if (Gate::denies('manage-learn', $request->site)) {
      return response()->json(['message' => 'Unauthorized'], 403);
    }
    $course = Course::where('site_id', $request->site->id)
      ->where('slug', $courseSlug)
      ->firstOrFail();

    $lesson = $course->lessons()
      ->where('slug', $slug)
      ->firstOrFail();

    $lesson->delete();
    return response()->json($lesson, 204);
  }
}
