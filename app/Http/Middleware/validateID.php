<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Device;
use App\Models\Manufacturer;
use App\Models\Product;

class validateID
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next){

      $device_id = $request->route()->parameter('device_id');
      $file_id = $request->route()->parameter('file_id');

      $product_id = $request->route()->parameter('product_id');
      $manufacturer_id = $request->route()->parameter('manufacturer_id');

      // device id was passed
      if($device_id != null){
        // check if the device id is numeric
        if(!is_numeric($device_id)){
          return response()->json([
            'Status' => 'Error',
            'Message' => 'ID must be numeric, you entered ' . $device_id,
            'Data' => ''
          ], 400);
        }

        // get the device from the db
        $device = Device::find($device_id);

        // store the device in the request
        $request->middlewareObjects = array('device' => $device);

        // the device with the id was not found in the db
        if($device == null){
          return response()->json([
            'Status' => 'Error',
            'Message' => 'The device with ID ' . $device_id . ' was not found...',
            'Data' => ''
          ], 404);
        }
      }

      // file id was passed
      if($file_id != null){

        // check if the file id is numeric
        if(!is_numeric($file_id)){
          return response()->json([
            'Status' => 'Error',
            'Message' => 'file ID must be numeric, you entered ' . $file_id,
            'Data' => ''
          ], 400);
        }

        // list of files for the device
        $list_files = $device->files;
        $file_result = null;

        // find the file 
        foreach($list_files as $file){
          if($file['id'] == $file_id){
            $file_result = $file;
          }
        }

        // store the file in the request
        $request->middlewareObjects['file'] = $file_result;

        // the file was not found
        if($file_result == null){
          return response()->json([
            'Status' => 'Error',
            'Message' => 'The file with ID ' . $file_id . ' was not found...',
            'Data' => ''
          ], 404);
        }
      }

      // product id was passed
      if($product_id != null){
        // check if the product id is numeric
        if(!is_numeric($product_id)){
          return response()->json([
            'Status' => 'Error',
            'Message' => 'product ID must be numeric, you entered ' . $product_id,
            'Data' => ''
          ], 400);
        }

        // try to get the product from the db
        $product = Product::find($product_id);
        
        // save the product in the request 
        $request->middlewareObjects = array('product' => $product);

        // product not found
        if($product == null){
          return response()->json([
            'Status' => 'Error',
            'Message' => 'The product with ID ' . $product_id . ' was not found...'
          ], 404);
        }
      }

      // manufacturer_id was passed 
      if($manufacturer_id != null){
        // check if the product id is numeric
        if(!is_numeric($manufacturer_id)){
          return response()->json([
            'Status' => 'Error',
            'Message' => 'product ID must be numeric, you entered ' . $manufacturer_id,
            'Data' => ''
          ], 400);
        }

        // try to get the product from the db
        $manufacturer = Manufacturer::find($manufacturer_id);
        
        // save the manufacturer in the request 
        $request->middlewareObjects = array('manufacturer' => $manufacturer);

        // product not found
        if($manufacturer == null){
          return response()->json([
            'Status' => 'Error',
            'Message' => 'The manufacturer with ID ' . $manufacturer_id . ' was not found...'
          ], 404);
        }
      }

      // return;
      return $next($request);
    }
    
}
