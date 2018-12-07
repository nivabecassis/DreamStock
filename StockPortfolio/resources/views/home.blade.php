@extends('layouts.app')

@section('content')
    <div class="container">
        @if(isset($portfolio))
            <div class="row justify-content-center mb-3">
                <div class="header p-3">
                    <div class="m-2 d-inline">Balance: ${{$portfolio['cash']}}</div>
                    <div class="m-2 d-inline">Member since: {{$portfolio['since']}}</div>
                    <div class="m-2 d-inline">Default currency: USD</div>
                    <div class="m-2 d-inline">Current value: ${{$portfolio['value']}}</div>
                    <div class="m-2 d-inline">Last close value: ${{$portfolio['closeValue']}}</div>
                    <div class="m-2 d-inline">Portfolio change: %{{$portfolio['portfolioChange']}}</div>
                </div>
            </div>
        @endif
        @if(isset($stocks))
            <h3>My stocks</h3>
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
                                                        class="btn d-inline action-btn-aligned" name="type" value="buy">
                                                    <i class="fa fa-plus"></i> Buy
                                                </button>
                                                <button type="submit" id="sell-stock-{{ $stock['symbol'] }}"
                                                        class="btn d-inline action-btn-aligned" name="type"
                                                        value="sell">
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
        @if(isset($stockPerform))
            <div class="mt-4">
                <h3>Stock to {{ $action }}: {{$stockPerform['symbol']}}</h3>
                <form action="{{ url('/home/transaction/' . $action .'/'.$stockPerform['symbol']) }}" method="POST">
                    <div class="card">
                        {{ csrf_field() }}
                        <table class="table-borderless">
                            <thead>
                            <tr>
                                <th scope="col">Symbol</th>
                                <th scope="col">Current Ask Price (USD)</th>
                                @if($stockPerform['currency'] != 'USD')
                                    <th scope="col">Current Ask Price ({{ $stockPerform['orig_currency'] }})</th>
                                @endif
                                <th scope="col">Transaction fee</th>
                                <th scope="col">Amount to {{ $action }}</th>
                                <th scope="col">Perform</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr class="stock_record rounded">
                                <td scope="col" class="p-2">{{$stockPerform['symbol']}}</td>
                                <td scope="col" class="p-2">{{$stockPerform['price']}}</td>
                                @if($stockPerform['currency'] != 'USD')
                                    <td scope="col" class="p-2">{{$stockPerform['orig_price']}}</td>
                                @endif
                                <td scope="col" class="p-2">{{ \Config::get('constants.options.TRANSACT_COST') }}</td>
                                <td class="p-2">
                                    <div class="resizeable-input-text center">
                                        <input type="number" id="share-count-{{ $stockPerform['symbol'] }}"
                                               class="d-inline form-control"
                                               placeholder="{{ $stockPerform['count'] }}" required
                                               name="share_count">
                                    </div>
                                </td>
                                <td class="p-2">
                                    <button type="submit" id="{{ $action }}-stock-{{ $stockPerform['symbol'] }}"
                                            class="btn d-inline">
                                        <i class="fa fa-plus"></i> {{ucwords($action)}}
                                    </button>
                                </td>
                            </tr>
                            </tbody>
                        </table>

                    </div>
                </form>
            </div>
        @endif

    <!--Error handling-->
        @if(isset($errorMsg))
            <div class="container text-center align-middle mt-4">
                <div class="header">
                    <h2>{{ $errorMsg }}</h2>
                </div>
            </div>
    @endif


    <!-- Buy stock form -->
        <div class="mt-4">

            <h3>Quotes</h3>
            <form action="{{ url('/home/quotes') }}" method="POST" class="form-horizontal">
            {{ csrf_field() }}

            <!-- Company tickers -->
                <div class="form-group">
                    <div class="d-inline">
                        <label for="ticker_symbol" class="control-label input-label">Get quotes for: </label>
                    </div>
                    <div class="d-inline">
                        <input required type="text" name="ticker_symbol" id="ticker_symbol" class="form-control input-text ml-2">
                    </div>
                    <div class="d-inline">
                        <button type="submit" class="btn btn-default">
                            <i class="fa fa-plus"></i> Confirm
                        </button>
                    </div>
                </div>

            </form>

            <!-- Get quotes -->
            @if(isset($quotes) && !isset($quotes["data"]))
                <h3>No quotes available</h3>
            @elseif(isset($quotes) && count($quotes["data"]) > 0)

                <div class="row justify-content-center">
                    <div class="col-md-12">
                        <div class="card">
                            <table class="table-borderless">
                                <thead>
                                <tr>
                                    <th scope="col">Company symbol</th>
                                    <th scope="col">Company name</th>
                                    <th scope="col">Shares</th>
                                    <th scope="col">Price</th>
                                    <th scope="col">Amount to buy</th>
                                </tr>
                                </thead>

                                <tbody>
                                @foreach ($quotes["data"] as $quote)
                                    <tr class="stock_record rounded">
                                        <td scope="col" class="p-2">{{$quote["symbol"]}}</td>
                                        <td scope="col" class="p-2">{{$quote["name"]}}</td>
                                        <td scope="col" class="p-2">{{$quote["shares"]}}</td>
                                        <td scope="col" class="p-2">{{$quote["price"]}}</td> 
                                        <td scope="col" class="p-2">
                                            <form class="form-inline justify-content-center"
                                                  action={{url("/home/transaction/buy/" . $quote["symbol"])}} method="POST">
                                                {{ csrf_field() }}
                                                <input required type="number" name="share_count" id="shares" class="form-control">
                                                <button type="submit" id="shares" class="btn d-inline ml-2">
                                                    Buy stock
                                                </button>
                                            </form>
                                        </td>
                                    </tr>

                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            @endif
        </div>
        </form>
    </div>

@endsection
