<!-- resources/views/common/errors.blade.php -->
@extends('layouts.app')

@section('content')
    @if (count($errors) > 0)
        <!-- Form Error List -->
        <section class="container">
            <section class="card">
                <div class="card-header">
                    <h4>Whoops! Something went wrong!</h4>
                </div>
                <div class="card-body">
                    <section class="list-group">
                        <h5>Errors:</h5>
                        <ul>
                            @foreach ($errors as $code => $error)
                                <li>{{ $code }} - {{ $error }}</li>
                            @endforeach
                        </ul>
                    </section>
                    <div class="form-group mt-4">
                        <form action="{{ url('/home/') }}" method="GET">
                            <button type="submit" class="btn">Back Home</button>
                        </form>
                    </div>
                </div>
            </section>
        </section>
    @endif
@endsection
