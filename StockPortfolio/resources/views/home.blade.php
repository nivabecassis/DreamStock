@extends('layouts.app')

@section('content')
    <div class="container">
        @if(isset($portfolio))
            <div class="row justify-content-center mb-3">
                <div class="header">
                    <div class="m-2 d-inline">Balance: ${{$portfolio['cash']}}</div>
                    <div class="m-2 d-inline">Member since: {{$portfolio['since']}}</div>
                    <div class="m-2 d-inline">Current value: ${{$portfolio['value']}}</div>
                    <div class="m-2 d-inline">Last close value: ${{$portfolio['closeValue']}}</div>
                </div>
            </div>
        @endif
        @if(isset($stocks))
            <div class="row justify-content-center">
                <div class="col-md-12">
                    <div class="card">
                        <table class="table-borderless">
                            <thead>
                            <tr>
                                <th scope="col">Symbol</th>
                                <th scope="col">Price</th>
                                <th scope="col">Previous Close</th>
                                <th scope="col">Change (%)</th>
                                <th scope="col">Owned</th>
                                <th scope="col">Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($stocks as $stock)
                                <tr class="stock_record rounded">
                                    <td>{{$stock['symbol']}}</td>
                                    <td>{{$stock['price']}}</td>
                                    <td>{{$stock['close_yesterday']}}</td>
                                    <td>{{$stock['change']}}</td>
                                    <td>{{$stock['count']}}</td>
                                    <td>
                                        <div class="form-group p-2">
                                            <form action="{{ url('/home/transaction/'.$stock['symbol']) }}"
                                                  method="GET">
                                                <button type="submit" id="buy-stock-{{ $stock['symbol'] }}"
                                                        class="btn d-inline" name="type" value="buy">
                                                    <i class="fa fa-plus"></i> Buy
                                                </button>
                                                <button type="submit" id="sell-stock-{{ $stock['symbol'] }}"
                                                        class="btn d-inline" name="type" value="sell">
                                                    <i class="fa fa-plus"></i> Sell
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
        @if(isset($stockToSell))
            <div class="mt-4">
                <h3>Selling Stock: {{$stockToSell['symbol']}}</h3>
                <form action="{{ url('/home/transaction/sell/'.$stockToSell['symbol']) }}" method="POST">
                    <div class="card">
                        {{ csrf_field() }}
                        <table class="table-borderless">
                            <thead>
                            <tr>
                                <th scope="col">Symbol</th>
                                <th scope="col">Current Ask Price (USD)</th>
                                @if($stockToSell['currency'] != 'USD')
                                    <th scope="col">Current Ask Price ({{ $stockToSell['orig_currency'] }})</th>
                                @endif
                                <th scope="col">Transaction fee</th>
                                <th scope="col">Amount To Sell</th>
                                <th scope="col">Perform</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr class="stock_record rounded">
                                <td scope="col">{{$stockToSell['symbol']}}</td>
                                <td scope="col">{{$stockToSell['price']}}</td>
                                @if($stockToSell['currency'] != 'USD')
                                    <td scope="col">{{$stockToSell['orig_price']}}</td>
                                @endif
                                <td scope="col">{{ \Config::get('constants.options.TRANSACT_COST') }}</td>
                                <td>
                                    <input type="number" id="share-count-{{ $stockToSell['symbol'] }}"
                                           class="d-inline"
                                           placeholder="{{ $stockToSell['count'] }}" required
                                           name="share_count">
                                </td>
                                <td>
                                    <button type="submit" id="sell-stock-{{ $stockToSell['symbol'] }}"
                                            class="btn d-inline">
                                        <i class="fa fa-plus"></i> Sell
                                    </button>
                                </td>
                            </tr>
                            </tbody>
                        </table>

                    </div>
                </form>
            </div>
        @endif
    </div>
    @if(isset($errorMsg))
        <div class="container text-center align-middle mt-4">
            <div class="header">
                <h2>{{ $errorMsg }}</h2>
            </div>
        </div>
    @endif
@endsection
