<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'Products';

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
      'product_name'
    ];

  public $timestamps=false;
}
