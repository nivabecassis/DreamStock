<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Stock Portfolio</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet" type="text/css">
        <link href="{{ asset('css/app.css') }}" rel="stylesheet">
        <link href="{{ asset('css/welcomepage.css') }}" rel="stylesheet">

    </head>
    <body>
        <nav class="navbar navbar-expand-lg fixed-top text-uppercase shadow-sm p-3 mb-5 bg-white rounded">
            <div class="container">
                <a href="{{ url('/') }}" class="navbar-brand">
                    <img src="{{ asset('svg/chart-line-solid.svg') }}" alt="chart line" class="brand-icon">
                    Stock Portfolio
                </a>
                <div class="collapse navbar-collapse">
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
        <footer class="footer">
            <span>© Stock Portfolio 2019. All Rights Reserved.</span>
        </footer>
    </body>
</html>
