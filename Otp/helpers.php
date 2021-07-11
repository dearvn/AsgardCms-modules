<?php

if (! function_exists('random_otp')) {

    function random_otp()
    {
        $type = setting('otp::otp_content');
        $len = setting('otp::otp_length');
        $opt_len = $len ? $len : 4;
        $otp = '';
        if ($type == 'Letter') {
            $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        } elseif ($type == 'Symbol') {
            $characters = '!"#$%&\'()*+,-./:;<=>?@[\]^_`{|}~';
        } else {
            $characters = '0123456789';
        }
        $charactersLength = strlen($characters) - 1;
        for ($i = 0; $i < $opt_len; $i++) {
            $otp .= $characters[rand(0, $charactersLength)];
        }
        return $otp;
    }
}