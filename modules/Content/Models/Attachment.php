<?php

namespace Modules\Content\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Attachment extends Model
{
  protected $table = 'content_attachments';

  use HasFactory, HasUuids;
  protected $appends = ['url'];

  public function getUrlAttribute() {
    return Storage::disk('digitalocean')->url($this->path);
  }
}
