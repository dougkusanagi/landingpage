<?php

namespace Modules\LandingPage\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\TemplateLandingPage\Entities\Category;
use Modules\TemplateLandingPage\Entities\Template;
use Modules\LandingPage\Entities\LandingPage;
use Modules\Ecommerce\Entities\LandingpageOrder;
use Modules\BlocksLandingPage\Entities\Block;
use Modules\Forms\Entities\FormData;
use URL;
use Modules\User\Entities\User;
use DB;
use Illuminate\Support\Facades\Artisan;
use Modules\LandingPage\Http\Helpers\MailChimp;
use Modules\LandingPage\Http\Helpers\Acellemail;
use Modules\Popup\Entities\Popup;
use Nahid\JsonQ\Jsonq;
use Str;
use Module;

class LandingPageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    
    public function dashboard(Request $request)
    {
        $user = $request->user();

        $stats_browser= DB::table('form_data')
            ->where('user_id', $user->id)
            ->selectRaw('count(id) AS total, browser')
            ->groupBy('browser')
            ->get();


        $stats_os= DB::table('form_data')
            ->where('user_id', $user->id)
            ->selectRaw('count(id) AS total, os')
            ->groupBy('os')
            ->get();
      

        $stats_device= DB::table('form_data')
            ->where('user_id', $user->id)
            ->selectRaw('count(id) AS total, device')
            ->groupBy('device')
            ->get();

        $date2 = new \Carbon\Carbon();
        $date1 = $date2->clone()->subDays(7);

        $start_date = $date1->toDateString();
        $end_date = $date2->toDateString();

        // line-chart data
        $data_days_visible = false;
        $data_days_chart = [];
        $date = $date1->clone();
        do {
            $data_days_chart[$date->toDateString()] = [
                'collectors' => 0,
            ];
            $date = $date->addDays(1);
        } while ($date2->toDateString() !== $date->toDateString());

        $colectors_results = \DB::select("
            SELECT 
                COUNT(id) AS total,
                DATE_FORMAT(created_at, '%Y-%m-%d') AS date
            FROM
                form_data
            WHERE
                user_id = '" . $user->id . "'
                AND created_at >= '" . $start_date . " 00:00:00'
                AND created_at <= '" . $end_date . " 23:59:59'
            GROUP BY date
            ORDER BY date ASC
        ");
        foreach ($colectors_results as $colectors_result) {
            $data_days_visible = true;
            $data_days_chart[$colectors_result->date]['collectors'] =  $colectors_result->total;
        }

        $data_days_chart = get_chart_data($data_days_chart);


        $landingpage_count = LandingPage::where('user_id', $request->user()->id)->count();
        $popup_count = 0;
        if(Module::find('Popup')){
            $popup_count = Popup::where('user_id', $request->user()->id)->count();
        }

        $total_order = LandingpageOrder::where('user_id', $request->user()->id)->count();
        $total_revenu = LandingpageOrder::where('status', '!=','CANCELED')->sum('total');

        $landingpage_unpublish = LandingPage::where('user_id', $request->user()->id)->unpublish()->count();
        $formdata_count = FormData::where('user_id', $request->user()->id)->count();

        return view('landingpage::dashboard', 
            compact('stats_browser','stats_os', 
                'stats_device','landingpage_count','formdata_count', 'landingpage_unpublish', 'popup_count', 'total_order', 'total_revenu', 'data_days_visible', 'data_days_chart')
        );
    }


    public function index(Request $request)
    {
        
        $data = LandingPage::where('user_id', $request->user()->id);

        if ($request->filled('search')) {
            $data->where('name', 'like', '%' . $request->search . '%');
        }

        $data->orderBy('updated_at', 'DESC');
        $data = $data->paginate(12);

        $categories = Category::get();

        $data2 = [
            'data' => $data,
            'categories' => $categories,
        ];

        return view('landingpage::landingpages.index', $data2);
    }
    

    public function clone($id,Request $request)
    {
        $page = LandingPage::findorFail($id);
        $item = $page->replicate();
        $item->name = "Copy ". $page->name;
        $item->sub_domain = generateRandomString(8).'.'.getAppDomain();
        $item->custom_domain = null;

        $item->save();
        
        return redirect()->route('landingpages.index')
            ->with('success', __('You copy the landing page :name successfully',['name'=>$page->name]));
    }

    public function getFonts(Request $request){
        $search_query = $request->search_query;
        $response = ['status' => true ,'data'=> []];
        if($search_query){
            $jsonq = new Jsonq(base_path().'/google-fonts.json');
            $result = $jsonq->from('items')->whereContains('family', $search_query)->get()->result();
            $response['status'] = true;
            $response['data'] = $result;
        }
        return response()->json($response);
        
    }

    public function save(Request $request)
    {
        // random subdomain
        $request->request->add(['sub_domain' => generateRandomString(8).'.'.getAppDomain()]);

        $validator = Validator::make($request->all(),
            [
            'name' => 'required|max:255',
            'template_id' => 'required',
            'sub_domain'     => 'regex:/^(?:[-A-Za-z0-9]+\.)+[A-Za-z]{2,6}$/|unique:landing_pages|min:5'
            ]
        );
        $sub_domain = $request->input('sub_domain');
        $template_id = $request->input('template_id');

        // Get template ID content and style => load builder
        $template = Template::find($template_id);

        if (!$template) {
            return redirect()->route('alltemplates')
            ->with('error', __('Template id not found'));
        }

        $template = replaceVarContentStyle($template);

        $item = "";

        if ($validator->fails()) {
            return redirect()->back()->with('error',$validator->errors()->first());
        }   
        else{

            $item = LandingPage::create([
                'user_id'  => $request->user()->id,
                'name' => $request->input('name'),
                'html' => $template->content,
                'css' => $template->style,
                'thank_you_page_html' => $template->thank_you_page,
                'thank_you_page_css' => $template->thank_you_style,
                'template_id' => $template_id,
                'sub_domain' => $sub_domain,
                'settings' => [
                    "intergration" => [
                        "type" => "none",
                        "settings" => [],
                    ],
                    "autoresponder" => [
                        "message_title" => "",
                        "sender_name" => "",
                        "sender_email" => "",
                        "message_text" => ""
                    ],
                    "fontCurrently" => "Open Sans"
                ]
            ]);

        }
        return redirect()->route('landingpages.builder', ['code'=>$item->code]);
    }
   


    public function updateBuilder(Request $request, $code, $type = 'main-page')
    {
        $type_arr = array('main-page','thank-you-page');
        
        if (!in_array($type, $type_arr)) {
            return response()->json(['error'=>__("Not Found Type")]);
        }

        if ($code) {
            
            $item = LandingPage::where('code', $code)->first();
            if ($item) {

                if ($type == 'thank-you-page') {
                    $item->thank_you_page_components = $request->input('gjs-components');
                    $item->thank_you_page_styles = $request->input('gjs-styles');
                    $item->thank_you_page_html = $request->input('gjs-html');
                    $item->thank_you_page_css = $request->input('gjs-css');
                }
                else{

                    $item->components = $request->input('gjs-components');
                    $item->styles = $request->input('gjs-styles');
                    $item->html = $request->input('gjs-html');
                    $item->css = $request->input('gjs-css');
                    $item->main_page_script = $request->input('main_page_script');
                    
                }
                
                
                if($item->save()){
                    return response()->json(['success'=>__("Updated successfully")]);
                }
            }
            
        }
        return response()->json(['error'=>__("Updated failed")]);
    }
   
    public function loadBuilder(Request $request, $code, $type = 'main-page'){
        
        $type_arr = array('main-page','thank-you-page');
        
        if (!in_array($type, $type_arr)) {
            return response()->json(['error'=>__("Not Found Type")]);
        }

        if ($code) {

            $page = LandingPage::where('user_id', $request->user()->id);
            $page = $page->where('code', $code)->first();
            if($page){
                
                if ($type == 'thank-you-page') {
                    return response()->json([
                        'gjs-components'=>$page->thank_you_page_components, 
                        'gjs-styles' => $page->thank_you_page_styles,
                        'gjs-html'=>$page->thank_you_page_html, 
                        'gjs-css' => $page->thank_you_page_css
                    ]);
                }
                return response()->json([
                    'gjs-components'=>$page->components, 
                    'gjs-styles' => $page->styles,
                    'gjs-html'=>$page->html, 
                    'gjs-css' => $page->css
                ]);
               
            }
            
        }
        abort(404);
    }
    

    public function builder(Request $request, $code, $type = 'main-page'){
        
        $type_arr = array('main-page','thank-you-page');
        if (!in_array($type, $type_arr)) {
            abort(404);
        }
       
        if ($code || !in_array($type,['main_page','thank-you-page'])) {

            $page = LandingPage::where('user_id', $request->user()->id);
            $page = $page->where('code', $code)->first();

            if($page){

                $blocks = Block::with('category')->where('active', true)->orderBy('name')->get();
                $blockscss = replaceVarContentStyle(config('app.blockscss'));
                $images_url = getAllImagesUser($request->user()->id);
                $all_icons = config('app.all_icons');

                return view('landingpage::landingpages.builder', compact('page','blocks','blockscss','images_url','all_icons'));
            }
            
        }
        abort(404);
    }

    public function setting($code,Request $request)
    {
        if ($code) {
            
            $data = LandingPage::where('user_id', $request->user()->id);
            
            $item = $data->where('code', $code)->first();
            

            // update default settings
            $dataDefault['settings'] = $item->settings;
            if (!isset($item->settings->intergration) || !isset($item->settings->autoresponder) || !isset($item->settings->fontCurrently)) {

                if (!isset($item->settings->intergration)){
                    $dataDefault['settings']['intergration'] = [
                        "type" => "none",
                        "settings" => []
                    ];
                }
                if (!isset($item->settings->autoresponder)){
                    $dataDefault['settings']['autoresponder'] = [
                        "message_title" => "",
                        "sender_name" => "",
                        "sender_email" => "",
                        "message_text" => ""
                    ];
                }
                if (!isset($item->settings->fontCurrently)){
                    $dataDefault['settings']['fontCurrently'] = 'Open Sans';
                }
                $item->update($dataDefault);
            }

            $item_intergration = $item->settings->intergration;
            $item_autoresponder = $item->settings->autoresponder;

            if ($item) {
                $intergrations_data = config('intergrations.data');
                return view('landingpage::landingpages.settings', compact('item','intergrations_data','item_intergration','item_autoresponder'));
            }
            
        }
        abort(404);
    }
    public function settingUpdate($id,Request $request)
    {
        // add validate intergration
        $intergration_type = $request->intergration_type;
        $autoresponder = $request->autoresponder;
        $validate_autoresponder = [];
        $validate_intergration = [];

        if (array_filter($autoresponder)) {
            $validate_autoresponder = [
                'autoresponder.message_title'    => 'required|max:255',
                'autoresponder.sender_name'    => 'required|max:50',
                'autoresponder.sender_email'    => 'required|email|max:50',
                'autoresponder.message_text'    => 'required',
            ];
        }
       
        if ($intergration_type == "mailchimp") {

            $validate_intergration = [
                'mailchimp.api_key'    => 'required',
                'mailchimp.contact_subscription_status'    => 'required',
                'mailchimp.mailing_list'    => 'required',
            ];
            
        }else if($intergration_type == "acellemail"){
            $validate_intergration = [
                'acellemail.api_endpoint'    => 'required|url',
                'acellemail.api_token'    => 'required',
                'acellemail.mailing_list'    => 'required',
            ];
        }
        else if($intergration_type == "mailwizz"){
            $validate_intergration = [
                'mailwizz.api_endpoint'    => 'required|url',
                'mailwizz.api_token'    => 'required',
                'mailwizz.mailing_list'    => 'required',
            ];
        }

        $rule_domain = "/^(?!:\/\/)(?=.{1,255}$)((.{1,63}\.){1,127}(?![0-9]*$)[a-z0-9-]+\.?)$/i";
        $validate = [
            'name'    => 'required',
            'domain_type'   => 'required|integer',
            'sub_domain'           => 'regex:'.$rule_domain.'|unique:landing_pages,sub_domain,' . $id,
            'custom_domain'     => 'regex:'.$rule_domain.'|unique:landing_pages,custom_domain,' . $id
        ];
        $validate = array_merge($validate, $validate_autoresponder);
        $validate = array_merge($validate, $validate_intergration);

        $request->validate($validate);


        if ($request->type_form_submit == 'url') {

            if (filter_var($request->redirect_url, FILTER_VALIDATE_URL) === FALSE) {
                return back()->with('error', __('Redirect URL Not a valid URL'));
            }
        }

        if ($request->type_payment_submit == 'url') {

            if (filter_var($request->redirect_url_payment, FILTER_VALIDATE_URL) === FALSE) {
                return back()->with('error', __('Redirect URL Not a valid URL'));
            }
        }

        $domain_main = '.'.getAppDomain();
        if (isset($request->sub_domain)) {
            # code...
            if (strpos($domain_main, $request->sub_domain)) {
                return back()->with('error', __('Subdomain must have ').$domain_main);
            }
        }
        

        $page  = LandingPage::findOrFail($id);

        $dataRequest = $request->all();
        
        if (!$request->filled('is_publish')) {
            $dataRequest['is_publish'] = false;
        } else {
            $dataRequest['is_publish'] = true;
        }
        
        // intergrations
        $intergration_setting = [
            "type" => "none",
            "settings" => [],
        ];

        

        switch ($intergration_type) {

            case 'mailchimp':

                $intergration_setting = [
                    "type" => $intergration_type,
                    "settings" => [
                        'api_key'    => $request->mailchimp['api_key'],
                        'contact_subscription_status'    => $request->mailchimp['contact_subscription_status'],
                        'mailing_list'    => $request->mailchimp['mailing_list'],
                        'merge_fields'  => $request->mailchimp['merge_fields'],
                    ]
                ];
                break;

            case 'acellemail':

                $intergration_setting = [
                    "type" => $intergration_type,
                    "settings" => [
                        'api_endpoint'    => $request->acellemail['api_endpoint'],
                        'api_token'    => $request->acellemail['api_token'],
                        'mailing_list'    => $request->acellemail['mailing_list'],
                        'merge_fields'  => $request->acellemail['merge_fields'],
                    ]
                ];
                break;
                
            case 'mailwizz':

                $intergration_setting = [
                    "type" => $intergration_type,
                    "settings" => [
                        'api_endpoint'    => $request->mailwizz['api_endpoint'],
                        'api_token'    => $request->mailwizz['api_token'],
                        'mailing_list'    => $request->mailwizz['mailing_list'],
                        'merge_fields'  => $request->mailwizz['merge_fields'],
                    ]
                ];
                break;
            
            default:
                break;
        }

        $dataRequest['settings'] = [
            "intergration" => $intergration_setting,
            "autoresponder" => $autoresponder,
            "fontCurrently" => $request->fontCurrently
        ];

        $favicon = $request->file('favicon');

        if ($favicon) {
            $new_name = 'favicon' . '.' . $favicon->getClientOriginalExtension();
            $favicon->move(public_path('storage/pages/'.$id.'/'), $new_name);
            $dataRequest['favicon'] = $new_name;
        }

        $social_image = $request->file('social_image');
        if ($social_image) {
            $new_name = 'social_image' . '.' . $social_image->getClientOriginalExtension();
            $social_image->move(public_path('storage/pages/'.$id.'/'), $new_name);
            $dataRequest['social_image'] = $new_name;
        }
        
        $page->update($dataRequest);

        return back()->with('success', __('Updated successfully'));
    }
    

    public function previewTemplate($id){
        
        if ($id) {

            $template = Template::find($id);

            if (!$template) {
                return redirect()->route('alltemplates')
                ->with('error', __('Template id not found'));
            }
            $template = replaceVarContentStyle($template);

            $item = $template;

            return view('landingpage::landingpages.preview_template', compact('item'));
            
        }
        abort(404);
    }
    public function getTemplateJson($id,Request $request)
    {
        $template = Template::find($id);
        if (!$template) {
            return response()->json([
                'error' => __('Template id not found')
            ]);
        }
        $template = replaceVarContentStyle($template);
        $blockscss = replaceVarContentStyle(config('app.blockscss'));

        return response()->json([
            'blockscss'=>$blockscss, 
            'style' => $template->style,
            'content'=>$template->content, 
            'thank_you_page' => $template->thank_you_page,
            'thank_you_style' => $template->thank_you_style,
        ]);
    }

    public function frameMainPage($id){
        
        if ($id) {

            $template = Template::find($id);
            if (!$template) {
                return redirect()->route('alltemplates')
                ->with('error', __('Template id not found'));
            }
            return view('landingpage::landingpages.frame_main_page', compact('template'));
            
        }
        abort(404);
    }
    public function frameThankYouPage($id){
        
        if ($id) {

            $template = Template::find($id);
            if (!$template) {
                return redirect()->route('alltemplates')
                ->with('error', __('Template id not found'));
            }
            return view('landingpage::landingpages.frame_thank_you_page', compact('template'));
            
        }
        abort(404);
    }

    public function delete($code,Request $request)
    {
        if ($code) {
            
            $data = LandingPage::where('user_id', $request->user()->id);

            $item = $data->where('code', $code)->first();

            if($item){
                $item->delete();
                return redirect()->route('landingpages.index')->with('success', __('Deleted successfully'));
            }
        }
        abort(404);
    }

    public function searchIcon(Request $request){
        
        $response = "";
        if ($request->keyword) {

            $input = preg_quote($request->keyword, '~'); 
            $data = config('app.all_icons');
            $result = preg_grep('~' . $input . '~', $data);
            
            foreach ($result as $key => $value) {
                # code...
                $response.= '<i class="'.$value.'"></i>';
            }
            return response()->json(['result'=> $response]);
        }
        else{

            $data = config('app.all_icons');
            foreach ($data as $key => $value) {
                # code...
                $response.= '<i class="'.$value.'"></i>';
            }
            return response()->json(['result'=> $response]);
        }


    }
    public function uploadImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
                'files' => 'required|mimes:jpg,jpeg,png,svg|max:20000',
        ]);
        if ($validator->fails()) {    
            return response()->json(['error' => __('The file must be an jpg,jpeg,png,svg')]);
        }
        $images=array();
        $imagesURL=array(); 

        if($request->hasfile('files'))
        {
            $file = $request->file('files');

            $name=$file->getClientOriginalName();
            $new_name = $name;
            $file->move(public_path('storage/user_storage/'.$request->user()->id), $new_name);
            $imagesURL[] = URL::to('/storage/user_storage/'.$request->user()->id."/".$new_name);
            $images[]=$new_name;

        }
        return response()->json($imagesURL);
    }

    public function deleteImage(Request $request)
    {
        $input=$request->all();
        $link_array = explode('/',$input['image_src']);
        $image_name = end($link_array);
        $path = public_path('storage/user_storage/'.$request->user()->id."/".$image_name);

        if(File::exists($path)) {
            File::delete($path);
        }
        return response()->json($image_name);
    }

    
}

