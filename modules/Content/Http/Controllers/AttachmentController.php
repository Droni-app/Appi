<?php

namespace Modules\Content\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;
use Modules\Content\Models\Attachment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Http\File;
use Illuminate\Support\Str;

class AttachmentController extends Controller
{
  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    $attachments = Attachment::where('site_id', $request->site->id);
    if(Auth::user()->enrollment) {
      $attachments = $attachments->where('user_id', $request->user_id);
    }
    if($request->has('search')) {
      $attachments = $attachments->where('name', 'like', '%'.$request->search.'%');
    }
    if($request->has('accept')) {
      $attachments = $attachments->where('mime_type', 'like', '%'.explode('/', $request->accept)[0].'%');
    }
    $attachments = $attachments->orderBy('created_at', 'desc')->paginate(24);

    return response()->json($attachments);
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request)
  {
    $request->validate([
      'file' => 'required|file|max:5120|mimes:gif,png,jpg,jpeg,pdf,doc,docx,xls,xlsx,ppt,pptx',
      'width' => 'integer',
      'height' => 'integer',
    ]);

    $attachment = new Attachment();
    $attachment->user_id = Auth::user()->id;
    $attachment->site_id = $request->site->id;
    $attachment->name = $request->file('file')->getClientOriginalName();
    // transform image
    if($request->width > 0 && $request->height > 0) {
      $img = Image::read($request->file('file'));
      $imgfinal = (string) $img->cover($request->width, $request->height)->toWebp(60);
      // Store File Temporary
      $randomStr = Str::random(40).".webp";
      Storage::disk('local')->put('tmp/'.$randomStr, $imgfinal);
      $imgfinal = new File(Storage::path('tmp/'.$randomStr));
      $attachment->path = Storage::disk('digitalocean')->putFile($request->site->id.'/'.Auth::user()->id, $imgfinal, 'public');
      // Delete temporary file
      Storage::disk('local')->delete('tmp/'.$randomStr);
    } else {
      $attachment->path = Storage::disk('digitalocean')->putFile($request->site->id.'/'.Auth::user()->id, $request->file('file'), 'public');
    }
    $attachment->provider = 'digitalocean';
    $attachment->size = $request->file('file')->getSize();
    $attachment->extension = $request->file('file')->extension();
    $attachment->mime_type = $request->file('file')->getMimeType();
    $attachment->save();

    return response()->json($attachment);
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Request $request, string $id)
  {
    $attachment = Attachment::where('site_id', $request->site->id)->findOrFail($id);
    if(Gate::denies('manage-attachment', $attachment)) { return response()->json(['message' => 'Unauthorized'], 403); }

    Storage::disk('digitalocean')->delete($attachment->path);
    $attachment->delete();

    return response()->json($attachment);
  }
}
