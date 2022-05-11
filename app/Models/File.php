<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class File extends Model
{
  use HasFactory;

  // fields we are able to change
  protected $fillable = [
  ];

  // protected $hidden = ['pivot', 'path'];
  protected $hidden = ['pivot'];

  // one file can belong to many devices, uses the pivot table
  public function devices(){
    return $this->belongsToMany(Device::class, 'Device_File', 'file_id', 'device_id')->withTimestamps();
  }

  public static function boot()
  {
    parent::boot();

    // on $file->delete()
    File::deleted(function($file){
      // delete the file from local storage
      Log::debug("Deleting the file " . $file->path . ' from storage');
      Storage::delete($file->path);
    });

  }
}
