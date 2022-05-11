<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *    title="Inventory Management System API",
 *    version="1.0.0",
 *    description="Hello my name is Tresten Pool aspiring backend developer. I created this api for my advanced software engineering class",
 *      @OA\Contact(
 *          email="trestenpool@gmail.com"
 *      )
 * )
*/

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}
