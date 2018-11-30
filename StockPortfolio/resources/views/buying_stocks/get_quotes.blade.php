@extends('layouts.app')

@section('content')

    <div class="panel-body">
        <!-- Display Validation Errors -->
    @include('common.errors')

    <!-- Buy stock form -->
        <form action="{{ url('portfolio_stock') }}" method="POST" class="form-horizontal">
        {{ csrf_field() }}

        <!-- Company tickers -->
            <div class="form-group">
                <label for="ticker_symbol" class="col-sm-3 control-label">Get quotes for: </label>

                <div class="col-sm-6">
                    <input type="text" name="name" id="ticker_symbol" class="form-control">
                </div>
            </div>

            <!-- Confirm button -->
            <div class="form-group">
                <div class="col-sm-offset-3 col-sm-6">
                    <button type="submit" class="btn btn-default">
                        <i class="fa fa-plus"></i> Buy Stock
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection