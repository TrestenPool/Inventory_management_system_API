<?php
namespace App\Http\Controllers;

use App\Models\Manufacturer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;


class ManufacturerController extends Controller
{
    /**
      * @OA\Get(
      *      path="/manufacturer",
      *      operationId="getManufacturerList",
      *      tags={"Manufacturers"},
      *      summary="Get all the manufacturers",
      *      @OA\Response(
      *          response=200,
      *          description="Successful operation"
      *       )
      *     )
    */
    public function index()
    {
      $data = DB::table('Manufacturer')->orderBy('auto_id')->get();

      return response()->json([
        'Status' => "OK",
        'Message' => '',
        'Data' => $data
      ]);
    }

    /**
      * @OA\Post(
      *   path="/manufacturer",
      *   operationId="storeNewManufacturer",
      *   tags={"Manufacturers"},
      *   summary="Store a new manufacturer",
      *   @OA\RequestBody(
      *     required=true,
      *     description="Pass in the manufacturer name",
      *     @OA\JsonContent(
      *       @OA\Property(
      *         property="manufacturer",
      *         @OA\Property(
      *           property="name",
      *           type="string",
      *           default=""
      *         )
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
      *     description="Invalid request body"
      *   ),
      *   @OA\Response(
      *     response=409,
      *     description="Conflict, there is already a manufacturer with that name"
      *   )
      *
      * )
    */
    public function store(Request $request)
    {
      $validator = Validator::make($request->all(), 
        // rules
        [
          'manufacturer.name' => 'required|max:127|unique:App\Models\Manufacturer,manufacturer_name'
        ],
        // messages
        [
          'manufacturer.name.required' => 'The manufacturer name is required. Pass in the request body manufacturer[name]',
          'manufacturer.name.unique' => 'The manufacturer name \':input\' is already taken'
        ]
      );

      // validation errors
      if ($validator->fails()) {

        // get the rules that were failed
        $failed_rules = $validator->failed();

        // the user did not pass in required field
        if(isset($failed_rules['manufacturer.name']['Required'])){
           $status_code = 400;
        }
        elseif (isset($failed_rules['manufacturer.name']['Unique'])) {
          $status_code = 409;
        }
        else{
          $status_code = 400;
        }

        $errors = $validator->errors()->all();
        return response()->json([
          'Status' => 'Error',
          'Message' => $errors,
          'Data' => ''
        ], $status_code);
      }

      // gets the manufacturer name from the request
      $manufacturer_name = $validator->validated()['manufacturer']['name'];

      // insert the record
      $data = Manufacturer::create([
        'manufacturer_name' => $manufacturer_name
      ]);

      // return 
      return response()->json([
        'Status' => 'OK',
        'Message' => '',
        'Data' => $data
      ], 200);
    }

    /**
      * @OA\Get(
      *   path="/manufacturer/{manufacturer_id}",
      *   operationId="GetManufacturer",
      *   tags={"Manufacturers"},
      *   summary="Get manufacturer by manufacturer_id",
      *   @OA\Parameter(
      *     name="manufacturer_id",
      *     in="path",
      *     description="Manufacturer id",
      *     required=true,
      *   ),
      *   @OA\Response(
      *     response=200,
      *     description="Success"
      *   ),
      *   @OA\Response(
      *     response=400,
      *     description="The manufacturer id route parameter is not valid. Must be numeric.."
      *   ),
      *   @OA\Response(
      *     response=404,
      *     description="manufacturer not found"
      *   )
      * )
    */
    public function show(Request $request, $manufacturer_id)
    {
      $manufacturer = $request->middlewareObjects['manufacturer'];

      return response()->json([
        'Status' => "OK",
        'Message' => '',
        'Data' => $manufacturer
      ], 200);
    }

    /**
      * @OA\Put(
      *   path="/manufacturer/{manufacturer_id}",
      *   operationId="updateManufacturer",
      *   tags={"Manufacturers"},
      *   summary="Update manufacturer by manufacturer_id",
      *   @OA\Parameter(
      *     name="manufacturer_id",
      *     in="path",
      *     description="Manufacturer id",
      *     required=true,
      *   ),
      *   @OA\RequestBody(
      *     required=true,
      *     description="Pass in the manufacturer name",
      *     @OA\JsonContent(
      *       @OA\Property(
      *         property="manufacturer",
      *         @OA\Property(
      *           property="name",
      *           type="string",
      *           default=""
      *         )
      *       )
      *     )
      *   ),
      *   @OA\Response(
      *     response=200,
      *     description="Success"
      *   ),
      *   @OA\Response(
      *     response=400,
      *     description="Invalid request body"
      *   ),
      *   @OA\Response(
      *     response=404,
      *     description="manufacturer not found"
      *   ),
      *   @OA\Response(
      *     response=409,
      *     description="Conflict, there is already a manufacturer with that name"
      *   )
      * )
    */
    public function update(Request $request, $manufacturer_id)
    {
      // create the validator
      $validator = Validator::make($request->all(), 
        // rules
        [
          'manufacturer.name' => ['required', 'max:127', Rule::unique('Manufacturer', 'manufacturer_name')->ignore($manufacturer_id, 'auto_id')]
        ],
        // messages
        [
          'manufacturer.name.required' => 'The manufacturer name is required. Pass manufacturer[name] through the request body',
          'manufacturer.name.unique' => 'The manufacturer name \':input\' is already taken' 
      ]);

      
      // run the validator
      if ($validator->fails()) {
        $failed_rules = $validator->failed();

        // setting the status code depending on the failed rule
        if(isset($failed_rules['manufacturer.name']['Required'])){
          $status_code = 400;
        }
        elseif(isset($failed_rules['manufacturer.name']['Unique'])){
          $status_code = 409;
        }
        else{
          $status_code = 400;
        }

        $errors = $validator->errors()->all();
        return response()->json([
          'Status' => 'Error',
          'Message' => $errors,
          'Data' => ''
        ], $status_code);
      }

      // gets the manufacturer name provided by the user
      $manufacturer_name = $validator->validated()['manufacturer']['name'];

      // get the manufacturer from the middleware
      $manufacturer = $request->middlewareObjects['manufacturer'];

      //update the record
      $manufacturer->manufacturer_name = $manufacturer_name;
      $result = $manufacturer->save();
      
      // there was an issue saving to the db
      if($result != 1){
        return response()->json([
          'Status' => 'Error',
          'Message' => 'There was an issue updating the record with manufacturer_id ' . $manufacturer_id, 
          'Data' => ''
        ], 500);
      }

      return response()->json([
        'Status' => 'OK',
        'Message' => '',
        'Data' => $manufacturer
      ], 200);
    }

    /**
      * @OA\Delete(
      *   path="/manufacturer/{manufacturer_id}",
      *   operationId="DeleteManufacturer",
      *   tags={"Manufacturers"},
      *   summary="Delete manufacturer by manufacturer_id",
      *   @OA\Parameter(
      *     name="manufacturer_id",
      *     in="path",
      *     description="Manufacturer id",
      *     required=true,
      *   ),
      *   @OA\Response(
      *     response=200,
      *     description="Success"
      *   ),
      *   @OA\Response(
      *     response=404,
      *     description="manufacturer not found with the given ID"
      *   )
      * )
    */
    public function destroy(Request $request, $manufacturer_id)
    {
      // get the manufacturer from the middleware
      $manufacturer = $request->middlewareObjects['manufacturer'];
      
      // attempt to delete the record
      $result = $manufacturer->delete();

      // there was an issue deleting the record
      if($result != 1){
        return response()->json([
          'Status' => 'Error',
          'Message' => 'There was an issue deleting the manufacturer with manufacturer_id ' . $manufacturer_id,
          'Data' => ''
        ], 500);
      }

      // success
      return response()->json([
        'Status' => 'OK',
        'Message' => '',
        'Data' => $manufacturer
      ], 200);

    }
}
