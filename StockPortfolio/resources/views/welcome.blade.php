<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <!-- Meta -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- Title -->
        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Scripts -->
        <script src="{{ asset('js/app.js') }}" defer></script>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet" type="text/css">

        <!-- CSS -->
        <link href="{{ asset('css/app.css') }}" rel="stylesheet">
        <link href="{{ asset('css/welcomepage.css') }}" rel="stylesheet">
    </head>
    <body>
        <nav class="navbar navbar-expand-lg fixed-top text-uppercase shadow-sm p-3 bg-white rounded navbar-light">
            <div class="container nav-text">
                <a href="{{ url('/') }}" class="navbar-brand">
                    <img src="{{ asset('svg/chart-line-solid.svg') }}" alt="chart line" class="brand-icon">
                    {{ config('app.name', 'Laravel') }}
                </a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon" style="background-image: url({{ asset('svg/custom-hamburger.svg') }});"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav ml-auto">
                        @if (Route::has('login'))
                                @auth
                                    <li class="nav-item mx-0 mx-lg-1">
                                        <a href="{{ url('/home') }}">Home</a>
                                    </li>
                                @else
                                    <li class="nav-item mx-0 mx-lg-1">
                                        <a href="{{ route('login') }}" class="btn btn-outline-primary my-2 my-sm-0">Login</a>
                                    </li>
                                    @if (Route::has('register'))
                                        <li class="nav-item mx-0 mx-lg-1">
                                            <a href="{{ route('register') }}" class="btn btn-primary my-2 my-sm-0">Join for free</a>
                                        </li>
                                    @endif
                                @endauth
                        @endif
                        </ul>
                </div>
            </div>
        </nav>
        <div id="color-bg" style="background-image: url({{ asset('images/bg-water.jpg') }});">
            <div class="flex-center position-ref full-height">
                <div class="content text-uppercase">
                    <div class="title font-weight-bold">
                        <span>Dream Stock</span>
                    </div>
                    <div class="slogan mb-5">
                        <span>Powered by TheBrogrammers ⚡</span>
                    </div>
                    @guest
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="btn btn-primary btn-lg">Get started</a>
                        @endif
                    @endguest
                </div>
            </div>
        </div>
        <footer class="footer">
            <span>© Dream Stock 2019. All Rights Reserved.</span>
        </footer>
    </body>
</html>
