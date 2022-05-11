<?php

namespace App\Http\Controllers;

use App\Models\Manufacturer;
use App\Models\Product;
use FFI\Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Facades\URL;
use PhpParser\Node\Stmt\Catch_;

class ProductController extends Controller
{

    /**
      * @OA\Get(
      *      path="/product",
      *      operationId="getProductsList",
      *      tags={"Products"},
      *      summary="Get all the products",
      *      @OA\Response(
      *       response=200,
      *       description="Successful operation",
      *      )
      * )
    */
    public function index()
    {
      $data = DB::table('Products')->orderBy('auto_id')->get();

      return response()->json([
        'Status' => 'OK',
        'Message' => '',
        'Data' => $data
      ], 200);
    }

    /**
      * @OA\Post(
      *   path="/product",
      *   operationId="storeNewProduct",
      *   tags={"Products"},
      *   summary="Store a new product",
      *   @OA\RequestBody(
      *     required=true,
      *     description="Pass in the product name",
      *     @OA\JsonContent(
      *       @OA\Property(
      *         property="product",
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
      *     description="Conflict, there is already a product with that name"
      *   )
      *
      * )
    */
    public function store(Request $request)
    {
      // create the validator
      $validator = Validator::make($request->all(), 
        // rules
        [
          'product.name' => 'required|max:128|unique:\App\Models\Product,product_name'
        ],
        // messages
        [
          'product.name.required' => 'The product name is required. Pass in product[name] through the request body',
          'product.name.unique' => 'A product with the name \':input\' already exists'
        ]
      );

      // run the validator
      if($validator->fails()){
        // get the rules that failed
        $failed_rules = $validator->failed(); 

        // the user did not pass in required field
        if(isset($failed_rules['product.name']['Required'])){
          $status_code = 400;
        }
        elseif (isset($failed_rules['product.name']['Unique'])) {
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

      // get the product name
      $product_name = $validator->validated()['product']['name'];

      // insert the record
      $data = Product::create([
        'product_name' => $product_name
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
      *   path="/product/{product_id}",
      *   operationId="GetProductID",
      *   tags={"Products"},
      *   summary="Get product by product id",
      *   @OA\Parameter(
      *     name="product_id",
      *     in="path",
      *     description="Product id",
      *     required=true,
      *   ),
      *   @OA\Response(
      *     response=200,
      *     description="Success"
      *   ),
      *   @OA\Response(
      *     response=400,
      *     description="The product id route parameter is not valid. Must be numeric.."
      *   ),
      *   @OA\Response(
      *     response=404,
      *     description="Product not found"
      *   )
      * )
    */
    public function show(Request $request, $product_id)
    {
      // get the product from the middleware we ran before
      $product = $request->middlewareObjects['product'];

      // return the product
      return response()->json([
        'Status' => "OK",
        'Message' => '',
        'Data' => $product
      ], 200);
    }

    /**
      * @OA\Put(
      *   path="/product/{product_id}",
      *   operationId="updateProduct",
      *   tags={"Products"},
      *   summary="Update an existing product",
      *   @OA\Parameter(
      *     name="product_id",
      *     in="path",
      *     description="Product id",
      *     required=true,
      *   ),
      *   @OA\RequestBody(
      *     required=true,
      *     description="Pass in the product name",
      *     @OA\JsonContent(
      *       @OA\Property(
      *         property="product",
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
      *     response=404,
      *     description="Product with the id provided was not found"
      *   ),
      *   @OA\Response(
      *     response=409,
      *     description="Conflict, there is already a product with that name"
      *   )
      *
      * )
    */
    public function update(Request $request, $product_id)
    {
      // get the product from the db
      $product = $request->middlewareObjects['product'];

      // create the validator
      $validator = Validator::make($request->all(),
        // rules
        [
          'product.name' => ['required', Rule::unique('Products', 'product_name')->ignore($product_id, 'auto_id')]
        ],
        // messages
        [
          'product.name.required' => 'The product name is required. Pass product[name] through the request body',
          'product.name.unique' => 'The product name \':input\' is already taken'
        ]
      );

      // attempt to validate the input
      if($validator->fails()){
        $failed_rules = $validator->failed();

        if( isset($failed_rules['product.name']['Required']) ){
          $status_code = 400;
        }
        elseif( isset($failed_rules['product.name']['Unique']) ){
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

      // get the product name
      $product_name = $validator->validated()['product']['name'];

      // update the record
      $product->product_name = $product_name;
      $result = $product->save();

      // there was an issue updating the product
      if($result != 1){
        return response()->json([
          'Status' => 'Error',
          'Message' => 'There was an issue updating the record with product_id ' . $product_id, 
          'Data' => ''
        ], 500);
      }

      // return the result
      return response()->json([
        'Status' => 'OK',
        'Message' => '',
        'Data' => $product
      ], 200);
    }

    /**
      * @OA\Delete(
      *   path="/product/{product_id}",
      *   operationId="DeleteProduct",
      *   tags={"Products"},
      *   summary="Delete a product by it's ID",
      *   @OA\Parameter(
      *     name="product_id",
      *     in="path",
      *     description="Product id",
      *     required=true,
      *   ),
      *   @OA\Response(
      *     response=200,
      *     description="Success"
      *   ),
      *   @OA\Response(
      *     response=404,
      *     description="Product not found"
      *   )
      * )
    */
    public function destroy(Request $request, $product_id)
    {
      // get the product from the middleware
      $product = $request->middlewareObjects['product'];

      // attempt to delete the record
      $result = $product->delete();

      // there was an issue deleting the product
      if($result != 1){
        return response()->json([
          'Status' => 'Error',
          'Message' => 'There was an issue deleting the product with product_id ' . $product_id,
          'Data' => ''
        ], 500);
      }

      // success
      return response()->json([
        'Status' => 'OK',
        'Message' => '',
        'Data' => $product
      ], 200);

    }


}
