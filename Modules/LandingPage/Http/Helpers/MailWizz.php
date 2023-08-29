<?php

namespace Modules\LandingPage\Http\Helpers;
use GuzzleHttp\Client;

class MailWizz
{
    protected $api_endpoint; 
    protected $api_token;            

    public function __construct($api_endpoint,$api_token)
    {
        // $api_endpoint = "https://sendboss.grupobigboss.com.br/api/index.php";
        // $api_token = "194e60d94170daf782e0342ecb05289910c03eaa";
        // $settings = $intergration['settings'];
        // $tags = config('app.name').",".$this->landing_page->name;
        // $acellemail = new MailWizz($api_endpoint, $api_token);
        // $response = $acellemail->addContact($settings, $form_data->field_values,$tags);
        // $mailWizz = new MailWizz($api_endpoint,$api_token);
        // $getMailLists = $mailWizz->getListMergeFields('yy934qey8madc');
        // dd($getMailLists);

        $this->api_endpoint = $api_endpoint;
        $this->api_token = $api_token;
        // $api_endpoint = "https://sendboss.grupobigboss.com.br/api/index.php";
        // $api_token = "194e60d94170daf782e0342ecb05289910c03eaa";
       
    }
    public function configMailWizz(){

        $config = new \EmsApi\Config([
            'apiUrl'    => $this->api_endpoint,
            'apiKey'    => $this->api_token,
            // components
        ]);
        \EmsApi\Base::setConfig($config);
    }

    public function getMailLists(){
       
        try {

            $this->configMailWizz();

            $endpoint = new \EmsApi\Endpoint\Lists();
            $response = $endpoint->getLists();

            $data = $response->body->toArray();
            if($data['status'] == 'success'){
                $records = $data['data']['records'];
                $result = [];
                if(count($records) > 0){
                    foreach($records as $item){
                        array_push($result, $item['general']);
                    }
                    return ['status'=> true, 'message' => __('Connected success'), 'data' => $result];
                }
            }
            return ['status'=> false, 'message' => __('API_KEY or Lists mail not found')];
        
        } catch (\Exception $e) {

            return ['status'=> false, 'message' => $e->getMessage()];
        }
        
    }
    
    public function getListMergeFields($list_id){ //yy934qey8madc

        try {

            $this->configMailWizz();
            $endpoint = new \EmsApi\Endpoint\ListFields();
            $response = $endpoint->getFields($list_id);
            $data = $response->body->toArray();
            if($data['status'] == 'success'){
                $records = $data['data']['records'];
                $result = [];
                if(count($records) > 0){
                    foreach($records as $item){
                        // only for type = "Text"
                        if ($item['type']['name'] == 'Text') {
                            array_push($result, $item['tag']);
                        }
                    }
                    return ['status'=> true, 'message' => __('Connected success'), 'data' => $result];
                }
            }elseif($data['status'] == 'error'){
                return ['status'=> false, 'message' => $data['error']];
            }
            return ['status'=> false, 'message' => __('API_KEY or Fields not found')];
        
        } catch (\Exception $e) {
            return ['status'=> false, 'message' => $e->getMessage()];
        }

    }
    

    public function addContact($settings, $field_values, $tags){
        
        try {
            $this->configMailWizz();

            $endpoint = new \EmsApi\Endpoint\ListSubscribers();
            
            $data_post = [
                'EMAIL'    => $field_values['email'],
            ];
            // custom_field
            if(isset($settings->merge_fields)){
                $merge_fields_setting = explode(",", $settings->merge_fields);
                
                foreach ($merge_fields_setting as $item) {
                    if (isset($field_values[$item]) &&  !empty($field_values[$item])) {
                        $data_post[$item] = $field_values[$item];
                    }
                }
            }
            $response = $endpoint->create($settings->mailing_list, $data_post);
            $data = $response->body->toArray();
            if($data['status'] == 'success'){
                return ['status'=> true, 'message' => __('MailWizz add contact success')];
            }
            return ['status'=> false, 'message' => $data['error']];
        
        } catch (\Exception $e) {
            return ['status'=> false, 'message' => $e->getMessage()];
        }

    }

}
