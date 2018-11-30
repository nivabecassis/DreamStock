@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Dashboard</div>
                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
                    <div>   
                        <p>Portfolio value: {{ $portfolioValue }} $USD</p>
                    </div>  
=======
>>>>>>> Add test
=======
                    <div>   
                        <p>Portfolio value: {{ $portfolioValue }} $USD</p>
                    </div>  
>>>>>>> Add getPortfolioValue function in PortfolioController
=======
                    <div>   
                        <p>Portfolio value: {{ $portfolioValue }} $USD</p>
                    </div>  
>>>>>>> a9dc27f89a05142765cd6e6e18407fd30af4323e
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
