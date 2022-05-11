<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Log;
use mysqli_sql_exception;
use PDOException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        // logging for sql errors
        $this->reportable(function(PDOException $p){
          Log::debug("sql error caught");
        })->stop();

        // response for sql errors
        $this->renderable(function(PDOException $pDOException, $request){
          // return response()->json([
          //   'Status' => 'Error',
          //   'Message' => $pDOException->getPrevious()->errorInfo[2],
          //   'Data' => ''
          // ], 500);
        });


        $this->reportable(function (Throwable $e) {
        });

    }
}
