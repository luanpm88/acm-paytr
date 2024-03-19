<html lang="en">
    <head>
        <title>{{ trans('paytr::messages.paytr') }}</title>
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
        <script type="text/javascript" src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
        <link rel="stylesheet" href="{{ \Acelle\Cashier\Cashier::public_url('/vendor/acelle-cashier/css/main.css') }}">
    </head>
    
    <body>
        <div class="main-container row mt-40">
            <div class="col-md-2"></div>
            <div class="col-md-4 mt-40 pd-60">
                <img class="rounded" width="80%" src="{{ $paytr->plugin->getIconUrl() }}" />
            </div>
            <div class="col-md-4 mt-40 pd-60">
                <h2 class="mb-40">{{ $invoice->title }}</h2>
                <p>{!! trans('paytr::messages.checkout.intro', [
                    'price' => format_price($invoice->total(), $invoice->currency->format),
                ]) !!}</p>

                @include('paytr::newCard')
                

                <div class="my-4">
                    <hr>
                    <form id="cancelForm" method="POST" action="{{ action('SubscriptionController@cancelInvoice', [
                                'invoice_uid' => $invoice->uid,
                    ]) }}">
                        {{ csrf_field() }}
                        <a href="javascript:;" onclick="$('#cancelForm').submit()">
                            {{ trans('paytr::messages.subscription.cancel_now_change_other_plan') }}
                        </a>
                    </form>
                    
                </div>

            </div>
            <div class="col-md-2"></div>
        </div>
        <br />
        <br />
        <br />

        
    </body>
</html>