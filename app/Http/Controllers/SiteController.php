<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Site;
use Illuminate\Support\Facades\Gate;

class SiteController extends Controller
{
  /**
   * Display the specified resource.
   */
  public function show(string $id)
  {
    // Mostrar detalles del sitio
    $site = Site::where('id', $id)->firstOrFail();
    return response()->json($site);
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, string $id)
  {
    // Validar permiso para editar sitio
    $site = Site::where('id', $id)->firstOrFail();
    if (Gate::denies('manage-site', $site)) {
      return response()->json(['message' => 'Unauthorized'], 403);
    }
    $request->validate([
      'name' => 'required|string|max:255',
      'domain' => 'required|string|max:255|unique:sites,domain,'.$id,
      'description' => 'nullable|string',
      'logo' => 'nullable|string',
      'icon' => 'nullable|string',
    ]);
    $site->name = $request->input('name');
    $site->domain = $request->input('domain');
    $site->description = $request->input('description');
    $site->logo = $request->input('logo');
    $site->icon = $request->input('icon');
    $site->save();
    return response()->json($site);
  }
}
