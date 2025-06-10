<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;

class staffController extends Controller
{
    //

   public function list($id=null){
        try{
            

            $saas_id=auth()->user()->saas_id;
           
            $totalCount = User::where('deleted_at','=',null)->count();
            $now = Carbon::now();
              
                $currentMonthCount = User::whereYear('created_at', $now->year)
                    ->whereMonth('created_at', $now->month)
                    ->where('deleted_at','=',null)
                    ->where('saas_id',$saas_id)
                    ->count();

                // Customers created in the current **year**
                $currentYearCount = User::whereYear('created_at', $now->year)
                       ->where('deleted_at','=',null)
                        ->where('saas_id',$saas_id)
                    ->count();

             if ($id !== null) {
                        // $customer = Customer::where('deleted_at','!=',null)->find($id);
                        $projects = User::where('deleted_at','=','0')->where('saas_id',$saas_id)->find($id);
                        
                        if (!$projects) {
                            return response()->json([
                                'status' => false,
                                'message' => 'Project not found.'
                            ]);
                        }
                        return response()->json([
                            'status' => true,
                            'data' => $projects
                        ]);
              }

        // Fetching all customers (Consider using pagination or selecting necessary fields if large dataset)
        $allprojects = User::where('deleted_at','=',value: '0')->where('role','SalesTeam')->where('saas_id',$saas_id)->orderBy('created_at', 'desc')->get();

        // Returning the response with data
        return response()->json([
            'status' => $totalCount > 0,
            'data' => $allprojects,
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
}
