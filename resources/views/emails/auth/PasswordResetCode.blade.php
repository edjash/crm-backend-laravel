@extends('emails.layout')
@section('title', $subject)
@section('content')
    <p>
        We received a request to reset the password on your <b>{{ $appName }}</b> account.
    </p>
    <p>
        Enter the below code to begin the reset:
    </p>
    <p style="font-size:24px; letter-spacing:3px">
        {{ $code }}
    </p>
@stop
