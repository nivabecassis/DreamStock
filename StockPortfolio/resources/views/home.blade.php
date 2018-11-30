@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center mb-3">
            <div class="header">
                <div class="m-2 d-inline ">Your Portfolio</div>
                <div class="m-2 d-inline">Balance: {{$portfolio['cash']}}</div>
                <div class="m-2 d-inline">Member since: {{$user->timestamps}}</div>
                <div class="m-2 d-inline">Current value: {{$portfolio['value']}}</div>
                <div class="m-2 d-inline">Last close value: {{$portfolio['closeValue']}}</div>
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
                                <tr class="stock_record rounded">
                                    <td>{{$stock['symbol']}}</td>
                                    <td>{{$stock['price']}}</td>
                                    <td>{{$stock['close_yesterday']}}</td>
                                    <td>{{$stock['day_change']}}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
