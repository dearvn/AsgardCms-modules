<?php
    $otp_methods = ['SMS' => 'SMS', 'Email' => 'E-Mail', 'SmsAndEmail' => 'SMS & E-Mail'];
    $otp_contents = ['Letter' => 'Letter', 'Number' => 'Number', 'Symbol' => 'Symbol'];
?>
<fieldset>
<div class="form-group">
    {!! Form::label($settingName, trans($moduleInfo['description'])) !!}
</div>
<div class="row">
    <div class='form-group col-md-4'>
        <label>Default Method</label>
        <select class="form-control" id="default_method"
                    name="{{ $settingName }}_default_method"
                    style="width: 300px;">
            <option value="">{{ trans('core::core.select') }}</option>
            @foreach($otp_methods as $key => $otp_method)
            <option {{ isset($dbSettings[$settingName."_default_method"]) && $dbSettings[$settingName."_default_method"]->plainValue == $key ? 'selected' : '' }}
                value="{{ $key }}">{{ $otp_method }}</option>
            @endforeach
        </select>
    </div>
    <div class='form-group col-md-4'>
        <label>Length</label>
        <input type="number" class="form-control" id="otp_length"
                    name="{{ $settingName }}_length"
                    value="{{ isset($dbSettings[$settingName.'_length']) ? $dbSettings[$settingName.'_length']->plainValue : ''}}"
                    style="width: 300px;">
    </div>
    <div class='form-group col-md-4'>
        <label>Length</label>
        <?php foreach ($otp_contents as $value => $otp_content): ?>
            <input id="{{ $otp_content }}"
                    name="{{ $settingName }}_content"
                    type="radio"
                    class="flat-blue"
                    {{ isset($dbSettings[$settingName."_content"]) && $dbSettings[$settingName."_content"]->plainValue == $value ? 'checked' : '' }}
                    value="{{ $value }}" />
            {{ $otp_content }}
        <?php endforeach; ?>
    </div>
</div>
<div class="row">
    <div class='form-group col-md-4'>
        <label>Expired In</label>
        <input type="number" class="form-control" id="otp_expired_in"
                    name="{{ $settingName }}_expired_in"
                    value="{{ isset($dbSettings[$settingName.'_expired_in']) ? $dbSettings[$settingName.'_expired_in']->plainValue : ''}}"
                    style="width: 300px;"> Minutes
    </div>
    <div class='form-group col-md-4'>
        <label>Max Failed</label>
        <input type="number" class="form-control" id="otp_max_failed"
                    name="{{ $settingName }}_max_failed"
                    value="{{ isset($dbSettings[$settingName.'_max_failed']) ? $dbSettings[$settingName.'_max_failed']->plainValue : ''}}"
                    style="width: 300px;"> Times
    </div>
</div>
<div class="row">
    <div class='form-group col-md-12'>
        <label>Message</label>
        <input class="form-control" id="otp_message"
                    name="{{ $settingName }}_message"
                    value="{{ isset($dbSettings[$settingName.'_message']) ? $dbSettings[$settingName.'_message']->plainValue : ''}}"
                    >
    </div>
</div>
</fieldset>