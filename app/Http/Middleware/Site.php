<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Site as SiteModel;

class Site
{
  /**
   * Handle an incoming request.
   *
   * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
   */
  public function handle(Request $request, Closure $next): Response
  {
    $request->validate([
      'siteId' => 'required|exists:sites,id'
    ]);
    $site = SiteModel::findOrFail($request->input('siteId'));
    $request->merge(['site' => $site]);
    return $next($request);
  }
}
