@extends('layouts.core.backend')

@section('title', trans('paytr::messages.paytr'))

@section('page_header')

    <div class="page-title">
        <ul class="breadcrumb breadcrumb-caret position-right">
            <li class="breadcrumb-item"><a href="{{ action("HomeController@index") }}">{{ trans('messages.home') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ action("Admin\PaymentController@index") }}">{{ trans('messages.payment_gateways') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('messages.update') }}</li>
        </ul>
        <h1>
            <span class="text-semibold">
                <span class="material-symbols-rounded">
                    payments
                </span>
                {{ trans('paytr::messages.paytr') }}</span>
        </h1>
    </div>

@endsection

@section('content')
    <h3 class="">{{ trans('paytr::messages.connection') }}</h3>
    <p>
        {!! trans('paytr::messages.settings.intro') !!}
    </p>

    <form enctype="multipart/form-data" action="{{ $paytr->gateway->getSettingsUrl() }}" method="POST" class="form-validate-jquery">
        {{ csrf_field() }}
        <div class="row">
            <div class="col-md-6">
                <div class="mb-4"> 
                    <label class="form-label required" for="merchant_id">{{ trans('paytr::messages.merchant_id.title') }}</label>
                    <input type="text" id="merchant_id" class="form-control {{ $errors->has('merchant_id') ? 'is-invalid' : '' }}"
                        name="merchant_id"
                        value="{{ $paytr->gateway->merchant_id }}"
                    />
            
                    @if ($errors->has('merchant_id'))
                        <div class="invalid-feedback"> {{ $errors->first('merchant_id') }} </div>
                    @endif
                </div>
                <div class="mb-4"> 
                    <label class="form-label required" for="merchant_key">{{ trans('paytr::messages.merchant_key.title') }}</label>
                    <input type="text" id="merchant_key" class="form-control {{ $errors->has('merchant_key') ? 'is-invalid' : '' }}"
                        name="merchant_key"
                        value="{{ $paytr->gateway->merchant_key }}"
                    />
            
                    @if ($errors->has('merchant_key'))
                        <div class="invalid-feedback"> {{ $errors->first('merchant_key') }} </div>
                    @endif
                </div>
                <div class="mb-4"> 
                    <label class="form-label required" for="merchant_salt">{{ trans('paytr::messages.merchant_salt.title') }}</label>
                    <input type="text" id="merchant_salt" class="form-control {{ $errors->has('merchant_salt') ? 'is-invalid' : '' }}"
                        name="merchant_salt"
                        value="{{ $paytr->gateway->merchant_salt }}"
                    />
            
                    @if ($errors->has('merchant_salt'))
                        <div class="invalid-feedback"> {{ $errors->first('merchant_salt') }} </div>
                    @endif
                </div>
            </div>
        </div>

        <div>
            <p class="mb-2">Copy this to Paytr Merchant Panel > Settings > Notification URL settings</p>
            <p style="margin-bottom: 30px" class="d-flex align-items-center">
                <code style="font-size: 18px" class="api-enpoint">{{ action('\Acelle\Paytr\Controllers\PaytrController@notification') }}</code>
                <button type="button" class="btn btn-secondary api-copy-button ml-4"><i class="material-symbols-rounded me-2">content_copy</i>{{ trans('messages.copy') }}</button>
            </p>
        </div>

        <script>
            $('.api-copy-button').on('click', function() {
                var code = $('.api-enpoint').html().trim();
    
                copyToClipboard(code);
    
                notify('success', '{{ trans('messages.notify.success') }}', '{{ trans('messages.api_endpoint.copied') }}');
            });
        </script>

        <div class="text-left">
            @if ($paytr->gateway->isActive())
                @if (!\Acelle\Library\Facades\Billing::isGatewayEnabled($paytr->gateway))
                    <input type="submit" name="enable_gateway" class="btn btn-primary me-1" value="{{ trans('cashier::messages.save_and_enable') }}" />
                    <button class="btn btn-default me-1">{{ trans('messages.save') }}</button>
                @else
                    <button class="btn btn-primary me-1">{{ trans('messages.save') }}</button>
                @endif
            @else
                <input type="submit" name="enable_gateway" class="btn btn-primary me-1" value="{{ trans('cashier::messages.connect') }}" />
            @endif

            @if($paytr->plugin->isActive())
                <a class="btn btn-default" href="{{ action('Admin\PaymentController@index') }}">{{ trans('cashier::messages.cancel') }}</a>
            @else
                <a class="btn btn-default" href="{{ action('Admin\PluginController@index') }}">{{ trans('cashier::messages.cancel') }}</a>
            @endif
        </div>

    </form>
       
@endsection