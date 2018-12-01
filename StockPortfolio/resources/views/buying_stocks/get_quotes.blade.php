@extends('layouts.app')

@section('content')

    <div class="panel-body">
        <!-- Display Validation Errors -->
    @include('common.errors')

    <!-- Buy stock form -->
        <form action="{{ url('/home') }}" method="POST" class="form-horizontal">
        {{ csrf_field() }}

        <!-- Company tickers -->
            <div class="form-group">
                <label for="ticker_symbol" class="col-sm-3 control-label">Get quotes for: </label>

                <div class="col-sm-6">
                    <input type="text" name="ticker_symbol" id="ticker_symbol" class="form-control">
                </div>
            </div>

            <!-- Confirm button -->
            <div class="form-group">
                <div class="col-sm-offset-3 col-sm-6">
                    <button type="submit" class="btn btn-default">
                        <i class="fa fa-plus"></i> Confirm
                    </button>
                </div>
            </div>
        </form>

        <!-- Get quotes -->
        @if(isset($quotes) && !isset($quotes["data"]))
            <h1>No quotes available</h1>
        @elseif(isset($quotes) && count($quotes["data"]) > 0)

            <table class="table-borderless">
                <thead>
                <tr class="stock_record rounded">
                    <th style="margin-right: 50px" scope="col">Company symbol</th>
                    <th style="margin-right: 50px" scope="col">Company name</th>
                    <th style="margin-right: 50px" scope="col">Shares</th>
                    <th style="margin-right: 50px" scope="col">Price</th>
                </tr>
                </thead>

                <tbody>
                @foreach ($quotes["data"] as $quote)

                    <tr>
                        <td style="margin-right: 50px">{{$quote["symbol"]}}</td>
                        <td style="margin-right: 50px">{{$quote["name"]}}</td>
                        <td style="margin-right: 50px">{{$quote["shares"]}}</td>
                        <td style="margin-right: 50px">{{$quote["price"]}}</td>
                        <td>
                            <form action={{url("/home/" . $quote["symbol"])}} method="POST">
                                {{ csrf_field() }}
                                <button type="submit" id="buy-stock-{{ $quote["symbol"] }}">
                                    Buy stock
                                </button>
                                <input type="text" name="shares" id="shares">
                            </form>
                        </td>
                    </tr>

                </tbody>
                @endforeach

            </table>

        @endif
    </div>
@endsection