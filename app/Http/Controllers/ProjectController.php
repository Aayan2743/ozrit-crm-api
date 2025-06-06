<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\project;
use Validator;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

class ProjectController extends Controller
{
    //

 
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
                        $projects = project::with(['customer','documents'])->where('deleted_at','=',null)->where('saas_id',$saas_id)->find($id);
                        
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
        $allprojects = project::with(['customer','documents'])->where('deleted_at','=',null)->where('saas_id',$saas_id)->get();

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

        // 3) Validate based on which key it is
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

        // 5) Return the updated project
        return response()->json([
            'status' => true,
            'data'   => $project->fresh(),
        ]);
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
