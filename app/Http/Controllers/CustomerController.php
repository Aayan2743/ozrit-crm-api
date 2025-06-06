<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\customer;
use Validator;
use Carbon\Carbon;
class CustomerController extends Controller
{
    //

    public function store(Request $request){

        $saas_id=auth()->user()->saas_id;
     
        
        
        $validator=validator::make($request->all(),[
            'fullName'=>'required',
            'mobile'=>'required|digits:10|unique:customers,mobile',
            'email'=>'required|email|unique:customers,email',
            'address'=>'required|string',
            'notes'=>'nullable|string',
        ]);

        if($validator->fails()){
            return response()->json([
                'status'=>false,
                'error'=>'Validation Error',
                'message'=> $validator->errors()->first()
            ]);
        }

        try{

             $saveCustomer=customer::create([
                'fullName'=>$request->fullName,
                'mobile'=>$request->mobile,
                'email'=>$request->email,
                'address'=>$request->address,
                'notes'=>$request->notes,
                'saas_id'=>auth()->user()->saas_id,
             ]);

             if($saveCustomer){
                 return response()->json([
                'status'=>true,
                'message'=> 'Customer Added Successfully...!'
                ]);
             }else{
                return response()->json([
                'status'=>false,
                'message'=> 'Customer Not Added'
                ]);
             }



        }catch(\Exception $e){
              return response()->json([
                'status'=>false,
                'error'=>'Exception Error',
                'message'=> $e->getMessage()
            ]);
        }

    }

    public function list($id=null){
        try{

               $saas_id=auth()->user()->saas_id;
            $totalCount = Customer::where('deleted_at','=',null)->count();
            $now = Carbon::now();
              
                $currentMonthCount = Customer::whereYear('created_at', $now->year)
                    ->whereMonth('created_at', $now->month)
                    ->where('deleted_at','=',null)
                    ->where('saas_id',$saas_id)
                    ->count();

                // Customers created in the current **year**
                $currentYearCount = Customer::whereYear('created_at', $now->year)
                       ->where('deleted_at','=',null)
                        ->where('saas_id',$saas_id)
                    ->count();

             if ($id !== null) {
                        // $customer = Customer::where('deleted_at','!=',null)->find($id);
                        $customer = Customer::where('deleted_at','=',null)->where('saas_id',$saas_id)->find($id);
                        
                        if (!$customer) {
                            return response()->json([
                                'status' => false,
                                'message' => 'Customer not found.'
                            ]);
                        }
                        return response()->json([
                            'status' => true,
                            'data' => $customer
                        ]);
              }

        // Fetching all customers (Consider using pagination or selecting necessary fields if large dataset)
        $allCustomers = Customer::where('deleted_at','=',null)->where('saas_id',$saas_id)->get();

        // Returning the response with data
        return response()->json([
            'status' => $totalCount > 0,
            'data' => $allCustomers,
            'total_count' => $totalCount,
            'current_month_count' => $currentMonthCount,
            'current_year_count' => $currentYearCount,
            'current_month' => $now->format('F'),
            'current_year' => $now->year
        ]);

    } catch (\Exception $e) {
        // Handle the exception
        return response()->json([
            'status' => false,
            'error' => 'Exception Error',
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString() // Optionally, add trace for debugging
        ]);

               
        }
    }


    public function destroy($id){
             $saas_id=auth()->user()->saas_id;
            $customer = customer::where('saas_id',$saas_id)->find($id);

            if ($customer) {
                $customer->delete();  // Delete the customer

                return response()->json([
                    'status' => true,
                    'message' => 'Customer deleted successfully'
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Customer not found'
                ], 404);
            }   


    }

    public function soft_destroy($id){
                   $saas_id=auth()->user()->saas_id;
            $customer = customer:: where('saas_id',$saas_id)->find($id);

            try{

                $soft_destroy_customer=customer::where('id',$id)->update([
                'deleted_at'=>now(),
               
               ]);

               if($soft_destroy_customer){
                    return response()->json([
                        'status'=>true,
                      
                        'message'=> 'Customer Deleted Successfully'
                    ]);
               }else{
                 return response()->json([
                        'status'=>false,
                        'message'=> 'Customer Not Deleted Successfully'
                    ]);
               }


            }catch(\Exception $e){
                    return response()->json([
                        'status'=>false,
                        'error'=>'Exception Error',
                        'message'=> $e->getMessage()
                    ]);
            }


    }

    public function update(Request $request,$id){
        $saas_id=auth()->user()->saas_id;
     
        // dd($request->all());
        
        $validator=validator::make($request->all(),[
            'fullName'=>'required',
             'mobile'   => "required|digits:10|unique:customers,mobile,{$id}",
             'email'    => "required|email|unique:customers,email,{$id}",
            'address'=>'required|string',
            'notes'=>'nullable|string',
        ]);

        if($validator->fails()){
            return response()->json([
                'status'=>false,
                'error'=>'Validation Error',
                'message'=> $validator->errors()->first()
            ]);
        }

        try{
            
             $updateCustomer=customer::where('id',$id)->where('saas_id',$saas_id)->update([
                'fullName'=>$request->fullName,
                'mobile'=>$request->mobile,
                'email'=>$request->email,
                'address'=>$request->address,
                'notes'=>$request->notes,
                'saas_id'=>auth()->user()->saas_id,
             ]);
                


            // $saveCustomer=customer::create([
            //     'fullName'=>$request->fullName,
            //     'mobile'=>$request->mobile,
            //     'email'=>$request->email,
            //     'address'=>$request->address,
            //     'notes'=>$request->notes,
            //     'saas_id'=>auth()->user()->saas_id,
            //  ]);

             if($updateCustomer){
                 return response()->json([
                'status'=>true,
                'message'=> 'Customer Updated Successfully...!'
                ]);
             }else{
                return response()->json([
                'status'=>false,
                'message'=> 'Customer Not Updated'
                ]);
             }



        }catch(\Exception $e){
              return response()->json([
                'status'=>false,
                'error'=>'Exception Error',
                'message'=> $e->getMessage()
            ]);
        }


    }


    
}
