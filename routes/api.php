<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomApiAuthController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\staffController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::get('/send-notification', [NotificationController::class, 'send']);



 

 



Route::group(['middleware'=>'api'],function($routes){


    //Mobile Api
    // Route::post('/mobile-login',[CustomApiAuthController::class,'mobile_login']);
    // // for this project below route use from super admin
    // Route::post('/register-user',[CustomApiAuthController::class,'register']);
    // Route::post('/register-mobile-otp',[CustomApiAuthController::class,'registerOtp']);
    // Route::post('/register-mobile-otp-verify',[CustomApiAuthController::class,'registerverifyOtp']);

    // login with Mgs91 
    Route::post('/login-otp',[CustomApiAuthController::class,'loginOtp']);
    Route::post('/verify-login-otp',[CustomApiAuthController::class,'verifyOtpLoginOTP']);

    // web Api
    Route::post('/web-login',[CustomApiAuthController::class,'web_login']);
    Route::post('/web-verify-login-otp',[CustomApiAuthController::class,'webverifyOtpLoginOTP']);

  
   // for both Web and Api only Email
    Route::post('/reset_password',[CustomApiAuthController::class,'reset_password_link']);
    Route::post('/verify_otp',[CustomApiAuthController::class,'verify_otp_update_password']);


  
 

});
    Route::get('/download-document/{id}',      [ProjectController::class, 'download_documents']);

// Route::group(['middleware'=>['jwt.verify', 'checkPos']],function($routes){
 Route::group(['middleware'=>['jwt.verify'],  'prefix' => 'admin'],function($routes){
    

    Route::post('add-customer',[CustomerController::class,'store']);
    Route::get('list-customer/{id?}',[CustomerController::class,'list']);
    Route::put('customer-update/{id}',[CustomerController::class,'update']);
    Route::delete('delete-customer/{id}',[CustomerController::class,'soft_destroy']);


    // Project Module
    Route::post('add-project',[ProjectController::class,'store']);
    Route::get('list-project/{id?}',[ProjectController::class,'list']);
    Route::put('/projects-stage-update/{id}',      [ProjectController::class, 'update']);
    Route::post('/project-document',      [ProjectController::class, 'add_documents']);
    Route::delete('project-document-delete/{id}',      [ProjectController::class, 'delete_documents']);
    // Route::get('/download-document/{filename}',      [ProjectController::class, 'download_documents']);
    
    //staff module
    Route::get('list-staff/{id?}',[staffController::class,'list']);


    //below no need

    Route::put('/projects-domain-update/{id}',      [ProjectController::class, 'updateDomain']);

    Route::put('/projects-hosting-update/{id}',      [ProjectController::class, 'updateHosting']);
    Route::put('/projects-design-update/{id}',      [ProjectController::class, 'updateDesign']);
    Route::put('/projects-live-update/{id}',      [ProjectController::class, 'updateMadeLive']);
    Route::put('/projects-balance-update/{id}',      [ProjectController::class, 'updateBalanceAsked']);



    // Route::get('get-profile',[ProfileController::class,'index']);


});

// Route::group(['middleware'=>['jwt.verify', 'checkManager']],function($routes){
    Route::group(['middleware'=>['jwt.verify', 'checkManager'],  'prefix' => 'manager'],function($routes){

    
    Route::get('get-profile',[ProfileController::class,'index']);




});
