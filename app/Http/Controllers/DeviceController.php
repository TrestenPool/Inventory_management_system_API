<?php

namespace App\Http\Controllers;
use App\Models\Device;
use App\Models\File;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpParser\Node\Expr\BinaryOp\Equal;
use Illuminate\Support\Facades\Storage;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class DeviceController extends Controller
{
  /**
    * @OA\Get(
    *   path="/device",
    *   operationId="getdeviceList",
    *   tags={"Devices"},
    *   summary="Index search for devices",
    *   description="The below query parameters are OPTIONAL. Feel free to mix and match",
    *   @OA\Parameter(
    *     name="pagination",
    *     in="query"
    *   ),
    *   @OA\Parameter(
    *     name="product_id",
    *     in="query"
    *   ),
    *   @OA\Parameter(
    *     name="manufacturer_id",
    *     in="query"
    *   ),
    *   @OA\Parameter(
    *     name="serial_number",
    *     in="query"
    *   ),
    *   @OA\Parameter(
    *     name="page",
    *     in="query"
    *   ),
    *   @OA\Response(
    *     response=200,
    *     description="Success"
    *   ),
    *   @OA\Response(
    *     response=400,
    *     description="Invalid Body"
    *    )
    * )
  */
  public function index(Request $request){
    // create the validator
    $validator = Validator::make($request->all(),
      [
        // 1 - 100 per page
        'pagination' => 'nullable|integer|between:10,100',
        'product_id' => 'nullable|exists:Products,auto_id|integer',
        'manufacturer_id' => 'nullable|exists:Manufacturer,auto_id|integer',
        'serial_number' => 'nullable|size:32'
      ]
    );

    // run the validator
    if($validator->fails()){
      // get all the error messages in an array format
      $messages = $validator->errors()->all();

      // return the error
      return response()->json([
        'Status' => 'Error',
        'Message' => $messages,
        'Data' => ''
      ], 400);
    }

    $pagination = 50; // default if 50 devices per page
    $product_id = null;
    $manufacturer_id = null;
    $serial_number = null;

    // gets the body of the request
    $body = $validator->validated();

    // pagination
    if( isset($body['pagination']) ){
      $pagination = $body['pagination'];
    }
    // product device_id
    if( isset($body['product_id']) ){
      $product_id = $body['product_id'];
    }
    // manufacturer device_id
    if( isset($body['manufacturer_id']) ){
      $manufacturer_id = $body['manufacturer_id'];
    }
    // serial number
    if( isset($body['serial_number']) ){
      $serial_number = $body['serial_number'];
    }

    // run the query
    $data = DB::table('Equipment')->where([
      ['product_id', ($product_id == null) ? 'like' : '=', ($product_id == null) ? '%' : $product_id],
      ['manufacturer_id', ($manufacturer_id == null) ? 'like' : '=', ($manufacturer_id == null) ? '%' : $manufacturer_id],
      ['serialNumber', ($serial_number == null) ? 'like' : '=', ($serial_number == null) ? '%' : $serial_number]
    ])->orderBy('auto_id')->simplePaginate($pagination);

    // only grab the juicy stuff
    $data = $data->items();

    // return response
    return response()->json([
      'Status' => 'OK',
      'Message' => '',
      'Data' => $data
    ], 200);
  }

  /**
    * @OA\Post(
    *   path="/device",
    *   operationId="newDevice",
    *   tags={"Devices"},
    *   summary="Store new device",
    *
    *   @OA\RequestBody(
    *     required=true,
    *     description="Pass in the device info",
    *     @OA\JsonContent(
    *       @OA\Property(
    *         property="device",
    *         @OA\Property(
    *           property="product_id",
    *           type="integer",
    *         ),
    *         @OA\Property(
    *           property="manufacturer_id",
    *           type="integer",
    *         ),
    *         @OA\Property(
    *           property="serial_number",
    *           type="string",
    *           default=""
    *         ),
    *       )
    *     )
    *   ),
    *
    *   @OA\Response(
    *     response=200,
    *     description="Success"
    *   ),
    *   @OA\Response(
    *     response=400,
    *     description="Invalid Body"
    *    ),
    *   @OA\Response(
    *     response=409,
    *     description="Conflict"
    *    )
    * )
  */
  public function store(Request $request){
    // create the validator
    $validator = Validator::make($request->all(),
      // rules
      [
        'device.product_id' => 'required|integer|exists:Products,auto_id',
        'device.serial_number' => 'required|alpha_num|size:32',

        'device.manufacturer_id' => [
          'required',
          'integer',
          'exists:Manufacturer,auto_id',
          Rule::unique('Equipment', 'manufacturer_id')->where(function($query) use($request) {
            return $query
              ->where('manufacturer_id', $request->get('device')['manufacturer_id'])
              ->where('serialNumber', $request->get('device')['serial_number']);
          })
          ]
      ],
      // messages
      [
        'device.manufacturer_id.unique' => 'There is already a device with the same manufacturer device_id and serial number as the one you provided'
      ]
    )->stopOnFirstFailure(true);

    // run the validator
    if($validator->fails()){
      // default code
      $status_code = 400;

      // get the errors in array format
      $messages = $validator->errors()->all();

      // check if conflict error
      $failed_messages = $validator->failed();
      if(isset($failed_messages['device.manufacturer_id']['Unique'])){
        $status_code = 409;
      }

      // return the error
      return response()->json([
        'Status' => 'Error',
        'Message' => $messages,
        'Data' => ''
      ], $status_code);
    }

    // get the body of the request
    $body = $validator->validated()['device'];

    // setup the device
    $device = new Device();
    $device->product_id = $body['product_id'];
    $device->manufacturer_id = $body['manufacturer_id'];
    $device->serialNumber = $body['serial_number'];

    // save the device
    $device->save();

    // return the response
    return response()->json([
      'Status' => 'OK',
      'Message' => '',
      'Data' => $device
    ], 200);
  }

  /**
    * @OA\Get(
    *   path="/device/{device_id}",
    *   operationId="getSpecificDevice",
    *   tags={"Devices"},
    *   summary="Get device by ID",
    *   @OA\Parameter(
    *     name="device_id",
    *     in="path",
    *     description="Device ID",
    *     required=true,
    *   ),
    *
    *   @OA\Response(
    *     response=200,
    *     description="Success"
    *   ),
    *   @OA\Response(
    *     response=400,
    *     description="Invalid Request"
    *    ),
    *   @OA\Response(
    *     response=404,
    *     description="Device not found"
    *    )
    * )
  */
  public function show(Request $request, $device_id){
    $device = $request->middlewareObjects['device'];

    // add the files to the device
    $files = $device->files;

    return response()->json([
        'Status' => 'OK',
        'Message' => '',
        'Data' => $device
    ], 200);
  }

  

  /**
    * @OA\Get(
    *   path="/device/{device_id}/file",
    *   operationId="showAllFiles",
    *   tags={"Device Files"},
    *   summary="Show all the files for the device",
    *
    *   @OA\Parameter(
    *     name="device_id",
    *     in="path",
    *     description="Device ID",
    *     required=true,
    *   ),
    *
    *   @OA\Response(
    *     response=200,
    *     description="Success"
    *   ),
    *   @OA\Response(
    *     response=400,
    *     description="Invalid Request"
    *    ),
    *   @OA\Response(
    *     response=404,
    *     description="Device not found"
    *    )
    * )
  */
  public function showFiles(Request $request, $device_id){
    // get the device from the middleware
    $device = $request->middlewareObjects['device'];

    // gets all the files for the device
    $device_files = $device->files;

    // return the response
    return response()->json([
      'Status' => 'OK',
      'Message' => '',
      'Data' => $device_files
    ], 200);
  }

  /**
    * @OA\Get(
    *   path="/device/{device_id}/file/{file_id}",
    *   operationId="showFileByID",
    *   tags={"Device Files"},
    *   summary="Retrieve a downloadable file from the device",
    *
    *   @OA\Parameter(
    *     name="device_id",
    *     in="path",
    *     description="Device ID",
    *     required=true,
    *   ),
    *   @OA\Parameter(
    *     name="file_id",
    *     in="path",
    *     description="File ID",
    *     required=true,
    *   ),
    *
    *   @OA\Response(
    *     response=200,
    *     description="Success"
    *   ),
    *   @OA\Response(
    *     response=400,
    *     description="Invalid Request"
    *    ),
    *   @OA\Response(
    *     response=404,
    *     description="Device or File not found"
    *    )
    * )
  */
  public function showFile(Request $request, $device_id, $file_id){
    // get the device from the db
    $file = $request->middlewareObjects['file'];

    return Storage::download($file->path, $file->name);
  }


  /**
    * @OA\Post(
    *   path="/device/{device_id}/file",
    *   operationId="AddFile",
    *   tags={"Device Files"},
    *   summary="Add a file to a device",
    *
    *   @OA\Parameter(
    *     name="device_id",
    *     in="path",
    *     description="Device ID",
    *     required=true,
    *   ),
    * 
    *   @OA\RequestBody(
    *    required=true,
    *    @OA\MediaType(
    *        mediaType="multipart/form-data",
    *        @OA\Schema(
    *            @OA\Property(
    *                property="file",
    *                description="file",
    *                type="file"
    *             ),
    *         ),
    *     ),
    *   ),
    *
    *   @OA\Response(
    *     response=200,
    *     description="Success"
    *   ),
    *   @OA\Response(
    *     response=400,
    *     description="Invalid Request"
    *    ),
    *   @OA\Response(
    *     response=404,
    *     description="Device not found"
    *    )
    * )
  */
  public function addFile(Request $request, $device_id){
    // get the device from the middleware
    $device = $request->middlewareObjects['device'];

    // create validator
    $validator = Validator::make($request->all(),[
      'file' => 'required|image|max:2048',
    ]);

    // run the validator
    if($validator->fails()) {
      $messages = $validator->errors()->all();
      $failed_messages = $validator->failed();

      return response()->json([
        'Status' => 'Error',
        'Message' => $messages,
        'Data' => ''
      ], 400);
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
    $file->devices()->attach($device_id);

    // return response
    return response()->json([
      'Status' => 'OK',
      'Message' => '',
      'Data' => array($file)
    ], 200);
  }

  /**
    * @OA\Post(
    *   path="/device/{device_id}/file/{file_id}",
    *   operationId="UpdateFile",
    *   tags={"Device Files"},
    *   summary="Update an existing file",
    *
    *   @OA\Parameter(
    *     name="device_id",
    *     in="path",
    *     description="Device ID",
    *     required=true,
    *   ),
    *   @OA\Parameter(
    *     name="file_id",
    *     in="path",
    *     description="File ID",
    *     required=true,
    *   ),
    * 
    *   @OA\RequestBody(
    *    required=true,
    *    @OA\MediaType(
    *        mediaType="multipart/form-data",
    *        @OA\Schema(
    *            @OA\Property(
    *                property="file",
    *                description="file",
    *                type="file"
    *             ),
    *         ),
    *     ),
    *   ),
    *
    *   @OA\Response(
    *     response=200,
    *     description="Success"
    *   ),
    *   @OA\Response(
    *     response=400,
    *     description="Invalid Request"
    *    ),
    *   @OA\Response(
    *     response=404,
    *     description="Device or file not found"
    *    )
    * )
  */
  public function updateFile(Request $request, $device_id, $file_id){
    // get the device and file from the middleware
    $device = $request->middlewareObjects['device'];
    $file = $request->middlewareObjects['file'];

    // create validator
    $validator = Validator::make($request->all(),[
      'file' => 'required|image|max:2048',
    ]);

    // run the validator
    if($validator->fails()) {
      $messages = $validator->errors()->all();
      $failed_messages = $validator->failed();

      return response()->json([
        'Status' => 'Error',
        'Message' => $messages,
        'Data' => ''
      ], 400);
    }

    // remove the original file from local storage
    Storage::delete($file->path);

    // get the file from the request body
    $file_body = $request->file('file');

    // store the file and save the path
    $path = $file_body->store('public/files');

    // get the original name of the file that the user passed in it as
    $name = $file_body->getClientOriginalName();

    // update the file fields
    $file->name = $name;
    $file->path= $path;

    // update the file in the db
    $file_update_result = $file->save();

    // there was an issue updating the file
    if($file_update_result != 1) {
      return response()->json([
        'Status' => 'Error',
        'Message' => 'There was an issue updating the file with ID ' . $file_id,
        'Data' => array()
      ], 500);
    }

    // return response
    return response()->json([
      'Status' => 'OK',
      'Message' => '',
      'Data' => array($file)
    ], 200);
  }

  /**
    * @OA\Delete(
    *   path="/device/{device_id}/file/{file_id}",
    *   operationId="DeleteFile",
    *   tags={"Device Files"},
    *   summary="Delete a file from a device",
    *
    *   @OA\Parameter(
    *     name="device_id",
    *     in="path",
    *     description="Device ID",
    *     required=true,
    *   ),
    *   @OA\Parameter(
    *     name="file_id",
    *     in="path",
    *     description="File ID",
    *     required=true,
    *   ),
    * 
    *
    *   @OA\Response(
    *     response=200,
    *     description="Success"
    *   ),
    *   @OA\Response(
    *     response=400,
    *     description="Invalid Request"
    *    ),
    *   @OA\Response(
    *     response=404,
    *     description="Device or file not found"
    *    )
    * )
  */
  public function deleteFile(Request $request, $device_id, $file_id){
    // get the device and file from the middleware
    $device = $request->middlewareObjects['device'];
    $file = $request->middlewareObjects['file'];

    // remove the device-file from the pivot table
    $device->files()->detach($file_id);
    
    // delete the file from the files db
    $file_delete_result = $file->delete();

    // there was an issue updating the file
    if($file_delete_result != 1) {
      return response()->json([
        'Status' => 'Error',
        'Message' => 'There was an issue deleting the file with ID ' . $file_id,
        'Data' => array()
      ], 500);
    }

    // return response
    return response()->json([
      'Status' => 'OK',
      'Message' => '',
      'Data' => array($file)
    ], 200);
  }


  /**
    * @OA\Put(
    *   path="/device/{device_id}",
    *   operationId="updateDevice",
    *   tags={"Devices"},
    *   summary="Update an existing device",
    *
    *   @OA\Parameter(
    *     name="device_id",
    *     in="path",
    *     description="Device ID",
    *     required=true,
    *   ),
    *
    *   @OA\RequestBody(
    *     description="Pass in the device info",
    *     @OA\JsonContent(
    *       @OA\Property(
    *         property="device",
    *         @OA\Property(
    *           property="product_id",
    *           type="integer",
    *         ),
    *         @OA\Property(
    *           property="manufacturer_id",
    *           type="integer",
    *         ),
    *         @OA\Property(
    *           property="serial_number",
    *           type="string",
    *           default=""
    *         ),
    *       )
    *     )
    *   ),
    *
    *   @OA\Response(
    *     response=200,
    *     description="Success"
    *   ),
    *   @OA\Response(
    *     response=400,
    *     description="Invalid Body"
    *    ),
    *   @OA\Response(
    *     response=404,
    *     description="Device not found"
    *    ),
    *   @OA\Response(
    *     response=409,
    *     description="Conflict"
    *    )
    * )
  */
  public function update(Request $request, $device_id){
    // get the device from the middleware
    $device = $request->middlewareObjects['device'];

    // create the validator
    $validator = Validator::make($request->all(),
      [
        'device.product_id' => 'nullable|integer|exists:Products,auto_id',
        'device.serial_number' => 'required_with:device.manufacturer_id|nullable|alpha_num|size:32',
        'device.manufacturer_id' => [
          'required_with:device.serial_number',
          'integer',
          'exists:Manufacturer,auto_id',
          Rule::unique('Equipment', 'manufacturer_id')->where(function($query) use($request) {
            return $query
              ->where('manufacturer_id', $request->get('device')['manufacturer_id'])
              ->where('serialNumber', $request->get('device')['serial_number']);
          })->ignore($device_id, 'auto_id')
        ]
      ],
      [
        'device.manufacturer_id.unique' => 'The manufacturer id and serial number you have entered is already taken...' 
      ]
    )->stopOnFirstFailure(true);

    // run the validator
    if($validator->fails()){
      // default code
      $status_code = 400;

      // get the errors in array format
      $messages = $validator->errors()->all();

      // check if conflict error
      $failed_messages = $validator->failed();
      if(isset($failed_messages['device.manufacturer_id']['Unique'])){
        $status_code = 409;
      }

      // return the error
      return response()->json([
        'Status' => 'Error',
        'Message' => $messages,
        'Data' => ''
      ], $status_code);
    }

    // get the validated input from the validator
    $validated_input = $validator->validated('device');

    // the user did not pass in a device, just return the device by the id
    if($validated_input == null){
      return response()->json([
        'Status' => 'OK',
        'Message' => '',
        'Data' => $device
      ], 200);
    }

    // the user passed in a device
    if( isset($validated_input['device']) ){
      // get the device object
      $updated_device_info = $validated_input['device'];

      // initialize update values to null
      $updated_product_id = null;
      $updated_manufacturer_id = null;
      $updated_serial_number = null;

      // product id passed
      if( isset($updated_device_info['product_id']) ){
        $updated_product_id = $updated_device_info['product_id'];
      }
      // manufacturer id passed
      if( isset($updated_device_info['manufacturer_id']) ){
        $updated_manufacturer_id = $updated_device_info['manufacturer_id'];
      }
      // serial number passed
      if( isset($updated_device_info['serial_number']) ){
        $updated_serial_number = $updated_device_info['serial_number'];
      }
    }

    // change the values of the device with the updated values if provided
    $device->product_id = $updated_product_id ?? $device->product_id;
    $device->manufacturer_id = $updated_manufacturer_id ?? $device->manufacturer_id;
    $device->serialNumber = $updated_serial_number ?? $device->serialNumber;

    // save back into the db
    $save_result = $device->save();

    // the device failed to save
    if($save_result != 1){
      return response()->json([
        'Status' => 'Error', 
        'Message' => 'There was an issue updating device with ID ' . $device_id,
        'Data' => ''
      ], 500);
    }
    
    // return successfull operation
    return response()->json([
      'Status' => 'OK',
      'Message' => '',
      'Data' => $device
    ], 200);
  }

  /**
    * @OA\Delete(
    *   path="/device/{device_id}",
    *   operationId="deleteDevice",
    *   tags={"Devices"},
    *   summary="Delete a device",
    *   @OA\Parameter(
    *     name="device_id",
    *     in="path",
    *     description="Device ID",
    *     required=true,
    *   ),
    *
    *   @OA\Response(
    *     response=200,
    *     description="Success"
    *   ),
    *   @OA\Response(
    *     response=400,
    *     description="Invalid Request"
    *    ),
    *   @OA\Response(
    *     response=404,
    *     description="Device not found"
    *    )
    * )
  */
  public function destroy(Request $request, $device_id){
    // get the device from the middleware
    $device = $request->middlewareObjects['device'];

    // delete the product along with all the files associated with the device from the db
    $delete_result = $device->delete();

    // issue deleting the device
    if($delete_result != 1){
      return response()->json([
        'Status' => 'Error',
        'Message' => 'There was an issue deleting the device with ID ' . $device_id,
        'Data' => ''
      ], 500);
    }

    // return the response
    return response()->json([
        'Status' => 'OK',
        'Message' => '',
        'Data' => $device
    ], 200);
  }


}
