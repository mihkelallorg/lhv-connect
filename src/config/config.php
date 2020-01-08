<?php

return [
    'tmp_folder' => "/tmp",
    'accounts' => [
        'name' => [
            'url' => "https://connect.lhv.eu",
            'cert' => [ // Your pivate key + LHV public certificate is combined into one file
                'path' => "",
                'password' => "",
            ],
            'IBAN' => "",
            'name' => "",
            'bic'  => "LHVBEE22",
        ],
        'name2' => [
            'url' => "https://connect.lhv.eu",
            'cert' => [ // Your pivate key + LHV public certificate is combined into one file
                'path' => "",
                'password' => "",
            ],
            'IBAN' => "",
        ],
        'name3' => [
            'url' => "https://connect.lhv.eu",
            'cert' => [ // LHV public certificate (lhv_public.crt / lhv_public.pem / ...)
                'path' => "",
                'password' => "",
            ],
            'ssl_key' => [ // Your pivate key (private.key / priv.pem / ...)
                'path' => "",
                'password' => "",
            ],
            'IBAN' => "",
            'name' => "",
            'bic'  => "LHVBEE22",
        ],
    ],

];
