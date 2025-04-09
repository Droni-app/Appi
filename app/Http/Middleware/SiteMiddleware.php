<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Site;
use Illuminate\Support\Facades\Validator;

class SiteMiddleware
{
  /**
   * Handle an incoming request.
   *
   * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
   */
  public function handle(Request $request, Closure $next): Response
  {
    $request->headers->set('Accept', 'application/json');
    Validator::make($request->headers->all(), [
      'site' => 'required|exists:sites,id'
    ])->validate();
    $site = Site::findOrFail($request->header('site'));
    $request->merge(['site' => $site]);
    return $next($request);
  }
}
