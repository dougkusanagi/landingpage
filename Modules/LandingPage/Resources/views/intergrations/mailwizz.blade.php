    

<div class="form-intergration d-none" id="form_mailwizz">
    
    <h4>@lang('Mailwizz')</h4>
    <div class="alert alert-warning" role="alert">
        @lang('The form will subscribe a new contact or lead to the chosen mailing system. Make sure there is an <strong>email</strong> field in the form!')
    </div>

    @if($item_intergration->type != "mailwizz")
        <input type="text" hidden="" name="mailwizz[merge_fields]" id="mailwizz_merge_fields" value="" class="form-control">
        <div class="form-group">
            <label class="form-label">@lang('API Endpoint')<span class="text-danger">*</span></label>
            <input type="url" id="mailwizz_api_endpoint" name="mailwizz[api_endpoint]" value="{{ env('mailwizz_api_endpoint') }}" placeholder="@lang('Your Mailwizz endpoint https://demo.mailwizz.com/api/index.php')" class="form-control">
        </div>
        <div class="form-group">
            <label class="form-label">@lang('API token')<span class="text-danger">*</span></label>
            <input type="text" id="mailwizz_api_token" name="mailwizz[api_token]" value="" placeholder="@lang('Your mailwizz API token OMVRVE986THjQZ...')" class="form-control">
        </div>
        <div class="form-group">
            <label class="form-label">@lang("Mailing list")<span class="text-danger">*</span></label>
            <select id="mailwizz_mailing_list" name="mailwizz[mailing_list]" class="form-control">
            </select>
        </div>
        <div class="alert alert-info" role="alert">
            @lang('Valid fields from your list'): <strong id="merge_fields_span_mailwizz"></strong>
       </div>
       <div class="alert alert-primary" role="alert">
           @lang('Change name your form fields with fields in your chosen integration, so that the data is saved correctly').<br>
           @lang('We suggest using TEXT type fields in Mailwizz lists')
       </div>
        
    @else
        <input type="text" hidden="" name="mailwizz[merge_fields]" id="mailwizz_merge_fields" value="{{$item_intergration->settings->merge_fields}}" class="form-control">
        <div class="form-group">
            <label class="form-label">@lang('API Endpoint')<span class="text-danger">*</span></label>
            <input type="url" id="mailwizz_api_endpoint" name="mailwizz[api_endpoint]" value="{{$item_intergration->settings->api_endpoint}}" placeholder="@lang('Your Mailwizz endpoint https://sendboss.grupobigboss.com.br/api/index.php')" class="form-control">
        </div>
        <div class="form-group">
            <label class="form-label">@lang('API token')<span class="text-danger">*</span></label>
            <input type="text" id="mailwizz_api_token" name="mailwizz[api_token]" value="{{$item_intergration->settings->api_token}}" placeholder="@lang('Your mailwizz API token OMVRVE986THjQZ...')" class="form-control">
        </div>
        <div class="form-group">
            <label class="form-label">@lang("Mailing list")<span class="text-danger">*</span></label>
            <select id="mailwizz_mailing_list" name="mailwizz[mailing_list]" class="form-control">
            </select>
        </div>
        <div class="alert alert-info" role="alert">
            @lang('Valid fields from your list'): <strong id="merge_fields_span_mailwizz"></strong>
       </div>
       <div class="alert alert-primary" role="alert">
           @lang('Change name your form fields with fields in your chosen integration, so that the data is saved correctly').<br>
           @lang('We suggest using TEXT type fields in Mailwizz lists')
       </div>

       
    @endif
</div>

