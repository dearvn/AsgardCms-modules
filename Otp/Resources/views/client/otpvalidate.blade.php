@extends('layouts.account')
@section('title')
    {{ trans('otp::messages.one_time_password') }} | @parent
@stop
@section('content')
<div class="card-header justify-content-center"><h4 class="card-title">{{trans("otp::messages.verify_phone_title")}}</h4></div>
    <div class="card-body">

    {!! Form::open(['route' => 'otp.verify']) !!}
    @csrf
    @include('partials.notifications')

    <div id="container-login">
            <p style="font-size:16px;line-height:1.2;margin-bottom:30px;text-align:center;">In order to validate your account, a One-Time Password has been sent to your cell phone or email. Please enter the code below to enter the system.
            <div class="form-group">
                <label class="sr-only" for="password">Enter One-Time Passord</label>
                <input type="password" class="form-control {{ $errors->has('code') ? ' is-invalid' : '' }}"
                    name="code" placeholder="Enter One-Time Passord" value="{{ old('code')}}">
                {!! $errors->first('code', '<div class="invalid-feedback">:message</div>') !!}
            </div>

            <div class="text-center">
                <button type="submit" id="btn-verify" class="btn btn-success btn-block">Verify</button>
            </div>				

    </div>

    {!! Form::close() !!}	

</div><!--/login-panel-->
@stop
