<?php

namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Http\Request;

// validation 
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class FileController extends Controller
{
  
  // upload file
  public function upload(Request $request){
    
    // create validator
    $validator = Validator::make($request->all(),[ 
      'file' => 'required|image|max:2048',
    ]);   
 
    // run the validator
    if($validator->fails()) {          
      return response()->json(['error'=>$validator->errors()], 401);                        
    } 

    // get the file from the request body
    $file_body = $request->file('file');

    // store the file and save the path
    $path = $file_body->store('public/files');

    // get the original name of the file that the user passed in it as
    $name = $file_body->getClientOriginalName();

    // save the file in the files table
    $file = new File();
    $file->name = $name;
    $file->path= $path;
    $file->save();
    
    // attempt to make the insert into the pivot table
    $file->devices()->attach(1);
    
    // return the response
    return response()->json([
      "success" => true,
      "message" => "File successfully uploaded",
      "file" => $file
    ]);
  }

}
