@extends('layouts.core.backend')

@section('title', trans('paytr::messages.paytr'))

@section('content')
<link rel="preconnect" href="https://fonts.googleapis.com"> 
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin> 
<link href="https://fonts.googleapis.com/css2?family=Righteous&display=swap" rel="stylesheet">

<style>
  .c-pp {
color: #6672c7;
}
  .hero__heading {
font-family: "Righteous",sans-serif;
font-size: 5rem;
font-weight: 500;
letter-spacing: -.02em;
line-height: 5.8rem;
margin-bottom: 2rem;
}
.hero__heading.alt {
font-size: 4rem;
line-height: 4.6rem;
}
.btn {
align-items: center;
background-color: #0a0e27;
border: none;
border-radius: 0.6rem;
color: #fff;
cursor: pointer;
display: inline-flex;
font-size: 1rem;
font-weight: 500;
justify-content: flex-start;
line-height: 1.1rem;
padding: 1.1rem 1.4rem;
text-align: center;
text-decoration: none;
transition: all .3s ease;
}

.btn--primary {
background-color: #1e2450;
color: #fff;
}
.btn--primary:hover {
  color: #fff;
  background-color: #333b71;
}

.btn--tertiary {
background-color: #eee;
color: #000;
}

.hero__ctas {
align-items: center;
display: flex;
justify-content: flex-start;
}
.hero__wrapper li {
  position: relative;
  list-style: none;
  line-height: 26px;
}
.hero__wrapper li:before {
  background-color: #262c55;
border-radius: 100%;
content: "";
height: 10px;
left: -25px;
position: absolute;
top: 7px;
width: 15px;
}

.hero__wrapper ul.main.purple li:before {
background-color: #6672c7;
}
.hero__wrapper ul.main.purple li:before {
background-color: #6672c7;
}
.hero__wrapper ul.main.purple li:before {
background-color: #6672c7;
}
</style>

<section class="hero alt p-5 pt-0">
  <div class="me-3 pe-2">
    <img width="180px" src="https://asset.brandfetch.io/idUYmgWbo8/idk8ncyyeQ.jpeg" alt="">
  </div>
  
  <div class="hero__wrapper row"><div class="hero__info col-12 col-md-6 pb-5">
    <h4 class="hero__heading alt">Sanal POS ve Ödeme Çözümleri | PayTR</h4>
  
  
  <p class="hero__sub-heading">
    PayTR Sanal POS ve Ödeme Çözümleri ile siz işinize odaklanın gerisini PayTR&#039;a bırakın. Güvenli ödeme altyapımız ile ödemelerinizi güvenle alın.
</p>

  <br>
    @if ($paytr->plugin->isActive())
      <a class="btn btn--primary me-3 mb-3" href="{{ action('\Acelle\Paytr\Controllers\PaytrController@settings') }}">
        {{ trans('paytr::messages.paytr_gateway_settings') }}
      </a>
      <a class="btn btn-default me-3 mb-3" href="{{ action('Admin\PaymentController@index') }}">
        {{ trans('paytr::messages.payment_settings') }}
      </a>
    @else
      
      @if (Auth::user()->admin->can('enable', $paytr->plugin) && $paytr->gateway->isActive())
          <a link-confirm="{{ trans('messages.enable_plugin_confirm') }}"
              href="{{ action('Admin\PluginController@enable', ["uids" => $paytr->plugin->uid]) }}"
              class="btn btn--primary me-1 mb-3"
          >
              {{ trans('messages.enable') }}
          </a>
      @endif
      <a class="btn btn-default me-3 mb-3" href="{{ action('\Acelle\Paytr\Controllers\PaytrController@settings') }}">
        {{ trans('paytr::messages.paytr_gateway_settings') }}
      </a>
    @endif

    <a href="https:/paytr.com" target="_blank" class="btn btn-default mb-3"><span class="btn__label">Go to paytr.com</span></a>
  
  </div> <div class="image-wrapper hero__media alt col-12 col-md-6 text-center" data-v-077b72aa=""><!----> <div class="image__inner" data-v-077b72aa="">
    <img width="400px" alt="hand mobile illustration" loading="lazy" data-src="1znLnSxQTEuHToc7v8cA" src="https://www.paytr.com/wp-content/uploads/slider-3-img.png" class="hero__media__item illustration" data-v-077b72aa=""></div></div></div></section>

@endsection