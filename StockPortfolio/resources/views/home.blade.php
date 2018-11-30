@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center mb-3">
            <div class="header">
                <div class="m-2 d-inline ">Your Portfolio</div>
                <div class="m-2 d-inline">Balance: {{$portfolio->cash_owned}}</div>
                <div class="m-2 d-inline">Member since: {{$user->timestamps}}</div>
            </div>
        </div>
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
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($stocks as $stock)
                            <tr>
                                <td>{{$stock['symbol']}}</td>
                                <td>{{$stock['price']}}</td>
                                <td>{{$stock['close_yesterday']}}</td>
                                <td>{{$stock['day_change']}}</td>
                            </tr>
                            <div class="card">
                                <div class="stock_info">{{$stock->ticker_symbol}}</div>
                                <div class="stock_info">Purchased on: {{$stock->purchase_date}}</div>
                                <div class="stock_info">Owned: {{$stock->share_count}}</div>
                            </div>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
