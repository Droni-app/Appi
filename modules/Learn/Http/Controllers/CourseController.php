<?php

namespace Modules\Learn\Http\Controllers;
use Illuminate\Routing\Controller;

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
    $courses = Course::where('site_id', $request->site_id)
      ->orderBy('created_at', 'desc')
      ->paginate($filters['perPage']);

    return response()->json($courses);
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request)
  {
    //
  }

  /**
   * Display the specified resource.
   */
  public function show(string $id)
  {
    //
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, string $id)
  {
    //
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(string $id)
  {
    //
  }
}
