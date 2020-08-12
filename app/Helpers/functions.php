<?php

function db() {
    return app('db');
}

function fcm() {
    return app('fcm');
}

function get_domain() {
    $subDomain = explode('.', $_SERVER['HTTP_HOST'])[0];

    $url = config()->get('url.' . $subDomain);

    return $url;
}

function set_translation($model, $datas) {
    foreach ($datas as $lang => $data) {
        foreach ($data as $key => $value) {
            if ($value !== null) {
                $model->translateOrNew($lang)->{$key} = $value;
            }
        }
    }

    return $model;
}

function real_boolean($data) {
        return filter_var($data, FILTER_VALIDATE_BOOLEAN);
}

function lower_class_basename($class) {
    return strtolower(class_basename($class));
}

function generate_random($digit = 10) {
    return substr(str_shuffle(
                str_repeat($x = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ', 
                    ceil($digit/strlen($x)) 
                )
            ), 1, $digit);
}

function random_number($digit = 10) {
    return rand(pow(10, $digit-1), pow(10, $digit)-1);
}

function empty_object() {
    return response()->json(['data' => new stdClass()]);
}

function parse_date_from_android($date) {
    return \Carbon\Carbon::create($date)->format('Y-m-d');
}

function parse_date_for_android($date) {
    return \Carbon\Carbon::create($date)->format('m/d/Y');
}

function rewrite_phone($phone, $with = '') {
    return preg_replace('/^(\+62|0|62)/', $with, $phone);
}

function csv_to_array($filename='', $header) {
    $delimiter=',';
        
    if (!file_exists($filename) || !is_readable($filename)) return FALSE;
    $data = [];

    if (($handle = fopen($filename, 'r')) !== FALSE) {
        while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
            $data[] = array_combine($header, $row);
        }
        fclose($handle);
    }

    return $data;
}

function unauth() {
    throw new \Illuminate\Auth\AuthenticationException('Unauthenticated');
}
