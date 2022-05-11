<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;


class Device extends Model
{
    use HasFactory;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'Equipment';

    /**
     * The primary key associated with the table.
     *
     * @var int
     */
    protected $primaryKey = 'auto_id';

    /**
     * Specifies which fields are allowed to be modified to the ORM object
     *
     * @var int
     */
    protected $fillable = [
      'product_id',
      'manufacturer_id', 
      'serialNumber'
    ];

  public $timestamps=false;
  protected $hidden = ['pivot', 'path'];

  // 1 device can belong to many files
  public function files(){
    return $this->belongsToMany(File::class, 'Device_File', 'device_id', 'file_id')->withTimestamps();
  }

  // event hanlder
  public static function boot() {
    parent::boot();

    // when calling $device->delete() this method will run first
    static::deleting(function($device) { 

      // go through all the files for the device
      foreach ($device->files as $file) {
        // remove the entry from the pivot table
        $device->files()->wherePivot('file_id', '=', $file->id)->detach();

        // delete the file
        $file->delete();
      }

    });
    
  }
}
