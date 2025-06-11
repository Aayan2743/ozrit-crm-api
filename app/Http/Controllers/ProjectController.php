<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\project;
use App\Models\project_document;
use App\Models\activitie;
use Validator;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
class ProjectController extends Controller
{
    //

 
public function updateproject(Request $request,$id){


 return response()->json([
    'sss'=>$customerId

 ]);


 $saas_id = auth()->user()->saas_id;

    // 1) Validation rules, including “array” and “*.mimes” for file uploads
    $validator = Validator::make($request->all(), [
        'customerId'          => 'required|integer|exists:customers,id',
        'projectTitle'        => 'required|string|max:255',
        'services'            => 'required|array',
        'services.*'          => 'string|max:100', // adjust max length as needed

        'totalPrice'          => 'required|numeric',
        'orderSource'         => 'required|string|max:100',
        'salesAgent'          => 'required|string|max:100',

        'domainName'          => 'required|string|max:255',
        'domainOwnership'     => 'required|string|max:50',
        'hostingType'         => 'required|string|max:50',
        'hasLogo'             => 'required|string|in:yes,no',
        'logoType'            => 'nullable|string|in:free,paid',
        'logoCost'            => 'nullable|numeric',

        'contactDetails'      => 'required|string|max:500',
        'deadline'            => 'required|date',
        'assignedTo'          => 'required|array',
        'assignedTo.*'        => 'string|max:100',

        // Attachments: zero or more PDF files
        'attachmentsx'         => 'nullable|array',
        'attachmentsx.*'       => 'file|mimes:pdf|max:20480', // max 20 MB each (adjust as needed)

        'notes'               => 'required|string|max:1000',
        'advancePaid'         => 'required|numeric',
        'balanceRemaining'    => 'required|numeric',
        'paymentMode'         => 'required|string|in:bank,upi',

        // Payment screenshots: zero or more images (jpg/png)
        'paymentScreenshots'  => 'nullable|array',
        // 'paymentScreenshots.*'=> 'image|mimes:jpeg,jpg,png|max:5120', // max 5 MB each
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status'  => false,
            'error'   => 'Validation Error',
            'message' => $validator->errors()->first()
        ]);
    }

    try {
        // 2) Handle file uploads
        $savedAttachmentPaths = [];
      
        if ($request->hasFile('attachmentsx')) {
         
                    foreach ($request->file('attachmentsx') as $pdf) {
                        $path = $pdf->store('project_attachments', 'public');
                        $savedAttachmentPaths[] = $path;
                    }
                }

        $savedScreenshotPaths = [];
        if ($request->hasFile('paymentScreenshots')) {
            /** @var \Illuminate\Http\UploadedFile $img */
            foreach ($request->file('paymentScreenshots') as $img) {
                // This will store under storage/app/public/payment_screenshots/{filename}
                $path = $img->store('payment_screenshots', 'public');
                $savedScreenshotPaths[] = $path;
            }
        }

        // 3) Create the project record
        // $newProject = project::create([
        //     'customerId'         => $request->customerId,
        //     'projectTitle'       => $request->projectTitle,
        //     // “services” was validated as an array of strings, so we can JSON‐encode it:
        //     'services'           => json_encode($request->services),

        //     'totalPrice'         => $request->totalPrice,
        //     'orderSource'        => $request->orderSource,
        //     'salesAgent'         => json_encode($request->salesAgent), 
            
        //     'domainName'         => $request->domainName,
        //     'domainOwnership'    => $request->domainOwnership,
        //     'hostingType'        => $request->hostingType,
        //     'hasLogo'            => $request->hasLogo,
        //     'logoType'           => $request->logoType,
        //     'logoCost'           => $request->logoCost,

        //     'contactDetails'     => $request->contactDetails,
        //     'deadline'           => $request->deadline,
        //     'assignedTo'         => json_encode($request->assignedTo),

        //     // Store the array of saved PDF paths as JSON
        //     'attachments'        => json_encode($savedAttachmentPaths),

        //     'notes'              => $request->notes,
        //     'advancePaid'        => $request->advancePaid,
        //     'balanceRemaining'   => $request->balanceRemaining,
        //     'paymentMode'        => $request->paymentMode,

        //     // Store the array of saved screenshot paths as JSON
        //     'paymentScreenshot'  => json_encode($savedScreenshotPaths),

        //     'saas_id'            => $saas_id,
        // ]);

        $updateProject=where('id',$id)->update([
            'customerId'         => $request->customerId,
            'projectTitle'       => $request->projectTitle,
            // “services” was validated as an array of strings, so we can JSON‐encode it:
            'services'           => json_encode($request->services),

            'totalPrice'         => $request->totalPrice,
            'orderSource'        => $request->orderSource,
            'salesAgent'         => json_encode($request->salesAgent), 
            
            'domainName'         => $request->domainName,
            'domainOwnership'    => $request->domainOwnership,
            'hostingType'        => $request->hostingType,
            'hasLogo'            => $request->hasLogo,
            'logoType'           => $request->logoType,
            'logoCost'           => $request->logoCost,

            'contactDetails'     => $request->contactDetails,
            'deadline'           => $request->deadline,
            'assignedTo'         => json_encode($request->assignedTo),

            // Store the array of saved PDF paths as JSON
            'attachments'        => json_encode($savedAttachmentPaths),

            'notes'              => $request->notes,
            'advancePaid'        => $request->advancePaid,
            'balanceRemaining'   => $request->balanceRemaining,
            'paymentMode'        => $request->paymentMode,

            // Store the array of saved screenshot paths as JSON
            'paymentScreenshot'  => json_encode($savedScreenshotPaths),

            'saas_id'            => $saas_id,
        ]);    

        if ($updateProject) {
            return response()->json([
                'status'  => true,
                'message' => 'Project updated Successfully!',
            ]);
        }

        return response()->json([
            'status'  => false,
            'message' => 'Project Not updated',
        ], 500);

    } catch (\Exception $e) {
        return response()->json([
            'status'  => false,
            'error'   => 'Exception Error',
            'message' => $e->getMessage()
        ], 500);
    }
}



    public function store(Request $request)
{

   
    $saas_id = auth()->user()->saas_id;

    // 1) Validation rules, including “array” and “*.mimes” for file uploads
    $validator = Validator::make($request->all(), [
        'customerId'          => 'required|integer|exists:customers,id',
        'projectTitle'        => 'required|string|max:255',
        'services'            => 'required|array',
        'services.*'          => 'string|max:100', // adjust max length as needed

        'totalPrice'          => 'required|numeric',
        'orderSource'         => 'required|string|max:100',
        'salesAgent'          => 'required|string|max:100',

        'domainName'          => 'required|string|max:255',
        'domainOwnership'     => 'required|string|max:50',
        'hostingType'         => 'required|string|max:50',
        'hasLogo'             => 'required|string|in:yes,no',
        'logoType'            => 'nullable|string|in:free,paid',
        'logoCost'            => 'nullable|numeric',

        'contactDetails'      => 'required|string|max:500',
        'deadline'            => 'required|date',
        'assignedTo'          => 'required|array',
        'assignedTo.*'        => 'string|max:100',

        // Attachments: zero or more PDF files
        'attachmentsx'         => 'nullable|array',
        'attachmentsx.*'       => 'file|mimes:pdf|max:20480', // max 20 MB each (adjust as needed)

        'notes'               => 'required|string|max:1000',
        'advancePaid'         => 'required|numeric',
        'balanceRemaining'    => 'required|numeric',
        'paymentMode'         => 'required|string|in:bank,upi',

        // Payment screenshots: zero or more images (jpg/png)
        'paymentScreenshots'  => 'nullable|array',
        // 'paymentScreenshots.*'=> 'image|mimes:jpeg,jpg,png|max:5120', // max 5 MB each
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status'  => false,
            'error'   => 'Validation Error',
            'message' => $validator->errors()->first()
        ]);
    }

    try {
        // 2) Handle file uploads
        $savedAttachmentPaths = [];
      
        if ($request->hasFile('attachmentsx')) {
         
                    foreach ($request->file('attachmentsx') as $pdf) {
                        $path = $pdf->store('project_attachments', 'public');
                        $savedAttachmentPaths[] = $path;
                    }
                }

        $savedScreenshotPaths = [];
        if ($request->hasFile('paymentScreenshots')) {
            /** @var \Illuminate\Http\UploadedFile $img */
            foreach ($request->file('paymentScreenshots') as $img) {
                // This will store under storage/app/public/payment_screenshots/{filename}
                $path = $img->store('payment_screenshots', 'public');
                $savedScreenshotPaths[] = $path;
            }
        }

        // 3) Create the project record
        $newProject = project::create([
            'customerId'         => $request->customerId,
            'projectTitle'       => $request->projectTitle,
            // “services” was validated as an array of strings, so we can JSON‐encode it:
            'services'           => json_encode($request->services),

            'totalPrice'         => $request->totalPrice,
            'orderSource'        => $request->orderSource,
            'salesAgent'         => json_encode($request->salesAgent), 
            
            'domainName'         => $request->domainName,
            'domainOwnership'    => $request->domainOwnership,
            'hostingType'        => $request->hostingType,
            'hasLogo'            => $request->hasLogo,
            'logoType'           => $request->logoType,
            'logoCost'           => $request->logoCost,

            'contactDetails'     => $request->contactDetails,
            'deadline'           => $request->deadline,
            'assignedTo'         => json_encode($request->assignedTo),

            // Store the array of saved PDF paths as JSON
            'attachments'        => json_encode($savedAttachmentPaths),

            'notes'              => $request->notes,
            'advancePaid'        => $request->advancePaid,
            'balanceRemaining'   => $request->balanceRemaining,
            'paymentMode'        => $request->paymentMode,

            // Store the array of saved screenshot paths as JSON
            'paymentScreenshot'  => json_encode($savedScreenshotPaths),

            'saas_id'            => $saas_id,
        ]);

        if ($newProject) {
            return response()->json([
                'status'  => true,
                'message' => 'Project Added Successfully!',
            ]);
        }

        return response()->json([
            'status'  => false,
            'message' => 'Project Not Added',
        ], 500);

    } catch (\Exception $e) {
        return response()->json([
            'status'  => false,
            'error'   => 'Exception Error',
            'message' => $e->getMessage()
        ], 500);
    }
}

    public function list($id=null){
        try{
            

               $saas_id=auth()->user()->saas_id;
            $totalCount = project::where('deleted_at','=',null)->count();
            $now = Carbon::now();
              
                $currentMonthCount = project::whereYear('created_at', $now->year)
                    ->whereMonth('created_at', $now->month)
                    ->where('deleted_at','=',null)
                    ->where('saas_id',$saas_id)
                    ->count();

                // Customers created in the current **year**
                $currentYearCount = project::whereYear('created_at', $now->year)
                       ->where('deleted_at','=',null)
                        ->where('saas_id',$saas_id)
                    ->count();

             if ($id !== null) {
                        // $customer = Customer::where('deleted_at','!=',null)->find($id);
                        $projects = project::with(['customer','documents','activities'])->where('deleted_at','=',null)->where('saas_id',$saas_id)->find($id);
                        
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
        $allprojects = project::with(['customer','documents',
        
        
          'activities' => function ($query) {
                 $query->orderBy('created_at', 'desc'); // ascending order for activities
              }
        
        
        
        ])->where('deleted_at','=',null)->where('saas_id',$saas_id)->orderBy('created_at', 'desc')->get();

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


     public function update(Request $request, $id)
    {
        // 1) Find the project or throw 404
        $project = Project::findOrFail($id);

        // 2) Determine which key was sent
        //    We only allow one of these five keys at a time.
        $allowedKeys = ['domain', 'hosting', 'design', 'madeLive', 'balanceAsked'];
        $sentKeys = array_intersect(array_keys($request->all()), $allowedKeys);

        if (count($sentKeys) !== 1) {
            return response()->json([
                'status' => false,
                'message' => 'You must send exactly one stage field.'
            ], 422);
        }

        $stageKey = reset($sentKeys);
        $value = $request->input($stageKey);

       
        $rules = [];
        switch ($stageKey) {
            case 'domain':
                $rules['domain'] = ['required', Rule::in(['Pending', 'Booked'])];
                break;

            case 'hosting':
                $rules['hosting'] = ['required', Rule::in(['Pending', 'Assigned'])];
                break;

            case 'design':
                $rules['design'] = [
                    'required',
                    Rule::in(['Not Started', 'In Progress', 'Client Changes', 'Done'])
                ];
                break;

            case 'madeLive':
                $rules['madeLive'] = ['required', Rule::in(['No', 'Yes'])];
                break;

            case 'balanceAsked':
                $rules['balanceAsked'] = ['required', Rule::in(['No', 'Yes'])];
                break;
        }

        // Run validation; if it fails, Laravel will return a 422 with errors
        $validated = $request->validate($rules);



        // 4) Assign and save only that one field
        $project->$stageKey = $validated[$stageKey];
        $project->save();


        // activities

            $create_activities=activitie::create([
                'project_id'=>$id,
                'stage'=>$stageKey,
                'details'=>$value,
                'updated_by'=>auth()->user()->name
            ]);

          



        // 5) Return the updated project
        return response()->json([
            'status' => true,
            'data'   => $project->fresh(),
        ]);
    }


    public function updateDocument(Request $request){
        


        $project = Project::findOrFail($request->id);

  
    $data = [];

    // 3) Handle the file upload if present
    if ($request->hasFile('file') && $request->file('file')->isValid()) {

        // dd("fgjdfklgjdflkg");
        $file     = $request->file('file');
        $filename = time() . '_' . $file->getClientOriginalName();
        
        // store in storage/app/public/documents
        $path = $file->storeAs('public/documents', $filename);


        $data['file_name']   = $path;
     
        $data['file_size']            = $file->getSize();

      


    }

    // 4) Capture other fields
    if ($request->filled('sentOn')) {
        $data['sentOn'] = $request->input('sentOn');
        // e.g. $project->sent_on = $data['sentOn'];
    }
    if ($request->filled('notes')) {
        $data['notes'] = $request->input('notes');
        // e.g. $project->notes = $data['notes'];
    }





    return response()->json([
        'status'  => true,
        'message' => 'Project document updated',
        'data'    => $data,
    ]);      


    }

    public function add_documents(Request $request){
            


              $validator = Validator::make($request->all(), [
                    'file' => 'nullable|file|max:10240',      // max 10MB
                    'sentOn'   => 'required|date',
                    'notes'    => 'nullable|string',
                    'forpurpose'    => 'required|string',   
                    'doc_type'    => 'required|string',   
                    // 'status'   => 'required|in:Pending,Sent,Received'

       
                 ]);

                        if ($validator->fails()) {
                            return response()->json([
                                'status'  => false,
                                'error'   => 'Validation Error',
                                'message' => $validator->errors()->first()
                            ]);
                        }

   
    $doc = new project_document();
    $doc->project_id   = $request->id;
    $doc->doc_type     = $request->doc_type;
    $doc->status       ='sent';
    $doc->sent_on      = Carbon::parse($request->input('sentOn'))->toDateString();
    // received_on stays null until client marks received
    $doc->notes        = $request->input('notes');

    // 4) Handle the file upload, if present
    if ($request->hasFile('file') && $request->file('file')->isValid()) {
        $file     = $request->file('file');
        $filename = time() . '_' . $file->getClientOriginalName();

        // store in storage/app/public/documents
        $path = $file->storeAs('public/documents', $filename);

        $url = Storage::url('documents/' . $filename);

        $doc->file_name = $url;             // or $path if you prefer full path
        $doc->doc_name = $request->forpurpose;             // or $path if you prefer full path
        $doc->file_size = $file->getSize();      // in bytes
    }

    // 5) Persist to the database
    $doc->save();


       $create_activities=activitie::create([
                'project_id'=>$request->id,
                'stage'=>$request->doc_type,
                'details'=>$request->forpurpose,
                'updated_by'=>auth()->user()->name
            ]);

    // 6) Return JSON response
    return response()->json([
        'status'  => true,
        'message' => 'Document saved successfully',
        'data'    => $doc,                      // the newly created document
    ]);
            



    }

    public function delete_documents(Request $request,$id){

        //   $filepath=project_document::delete($id);

          $project_document = project_document::find($id);

            if ($project_document) {
                $project_document->delete();  // Delete the customer

                return response()->json([
                    'status' => true,
                    'message' => 'Project Document deleted successfully'
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Project Document Not deleted successfully'
                ], 201);
            }   

         


          


    }


    public function download_documents(Request $request, $id){

            $filepath=project_document::find($id);
            return response()->json([
                'status'=>"dfhgdf",
                'ddd'=>$filepath->file_name
            ]);


        //  $validator = Validator::make($request->all(), [
        // 'filename'          => 'required|string',
       
        //      ]);

        //             if ($validator->fails()) {
        //                 return response()->json([
        //                     'status'  => false,
        //                     'error'   => 'Validation Error',
        //                     'message' => $validator->errors()->first()
        //                 ]);
        //             }

        //               $filePath = env('APP_URL') . $filename;

        // // Check if file exists
        // if (!Storage::exists($filePath)) {
        //     return response()->json([
        //         'error' => 'File not found'
        //     ], 404);
        // }

        // // Get the file's mime type
        // $mimeType = Storage::mimeType($filePath);

        // // Create response headers
        // $headers = [
        //     'Content-Type' => $mimeType,
        //     'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        // ];

        // // Return streamed response for file download
        // return new StreamedResponse(function () use ($filePath) {
        //     // Read and output the file in chunks
        //     $stream = Storage::readStream($filePath);
        //     fpassthru($stream);
        //     fclose($stream);
        // }, 200, $headers);
    }






    // no need below code

    public function updateDomain(Request $request, $id)
    {
        $project = Project::findOrFail($id);

        $validated = $request->validate([
            'domain' => ['required', Rule::in(['Pending', 'Booked'])],
        ]);

        $project->domain = $validated['domain'];
        $project->save();

        return response()->json([
            'status' => true,
            'data'   => $project->fresh(),
        ]);
    }

     public function updateHosting(Request $request, $id)
    {
        $project = Project::findOrFail($id);

        $validated = $request->validate([
            'hosting' => ['required', Rule::in(['Pending', 'Assigned'])],
        ]);

        $project->hosting = $validated['hosting'];
        $project->save();

        return response()->json([
            'status' => true,
            'data'   => $project->fresh(),
        ]);
    }

    /**
     * PATCH /api/projects/{id}/design
     * Body: { "design": "In Progress" }
     */
    public function updateDesign(Request $request, $id)
    {
        $project = Project::findOrFail($id);

        $validated = $request->validate([
            'design' => [
                'required',
                Rule::in(['Not Started', 'In Progress', 'Client Changes', 'Done'])
            ],
        ]);

        $project->design = $validated['design'];
        $project->save();

        return response()->json([
            'status' => true,
            'data'   => $project->fresh(),
        ]);
    }

    /**
     * PATCH /api/projects/{id}/made-live
     * Body: { "madeLive": "Yes" }
     */
    public function updateMadeLive(Request $request, $id)
    {
        $project = Project::findOrFail($id);

        $validated = $request->validate([
            'madeLive' => ['required', Rule::in(['No', 'Yes'])],
        ]);

        $project->madeLive = $validated['madeLive'];
        $project->save();

        return response()->json([
            'status' => true,
            'data'   => $project->fresh(),
        ]);
    }

    /**
     * PATCH /api/projects/{id}/balance-asked
     * Body: { "balanceAsked": "Yes" }
     */
    public function updateBalanceAsked(Request $request, $id)
    {
        $project = Project::findOrFail($id);

        $validated = $request->validate([
            'balanceAsked' => ['required', Rule::in(['No', 'Yes'])],
        ]);

        $project->balanceAsked = $validated['balanceAsked'];
        $project->save();

        return response()->json([
            'status' => true,
            'data'   => $project->fresh(),
        ]);
    }

}
