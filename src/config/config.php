<?php

return [
    'tmp_folder' => "/tmp",
    'accounts' => [
        'name' => [
            'url' => "https://connect.lhv.eu",
            'cert' => [ // LHV public certificate / public.pem
                'path' => "",
                'password' => "",
            ],
            'ssl_key' => [ // Your pivate key / private.key / private.pem / PEM format of private.p12 key
                'path' => null,
            ],
            'IBAN' => "",
            'name' => "",
            'bic'  => "LHVBEE22",
        ],
        'name2' => [
            'url' => "https://connect.lhv.eu",
            'cert' => [ // LHV public certificate / public.pem
                'path' => "",
                'password' => "",
            ],
            'ssl_key' => [ // Your pivate key / private.key / private.pem / PEM format of private.p12 key
                'path' => null,
            ],
            'IBAN' => "",
        ],
    ],

];
