@extends('emails.layout')
@section('title', $subject)
@section('content')
    <p>
        Your user account with the e-mail address <b>{{ $email }}</b> has been created.
    </p>
    <p>
        Welcome to {{ $appName }}!
    </p>
@stop
