<?php

return array(
    'error_code' => array(
        // 通用的错误码
        'SUCCESS'                         => '0',
        'INTERNAL_SERVER_ERROR'           => '10000',
        'REQUEST_TYPE_ERROR'              => '10001',
        'VALIDATE_CLASS_NOT_EXISTS'       => '10002',
        'SIGN_REQUIRE'                    => '10003',
        'SIGN_LENGTH_ERROR'               => '10004',
        'TIMESTAMP_REQUIRE'               => '10005',
        'TIMESTAMP_TYPE_FAIL'             => '10006',
        'NONCE_REQUIRE'                   => '10007',
        'NONCE_LENGTH_ERROR'              => '10008',
        'NONCE_TYPE_FAIL'                 => '10009',
        'TIMESTAMP_EXPIRE'                => '10010',
        'NONCE_ALREADY_EXISTS'            => '10011',
        'SIGN_ERROR'                      => '10012',
        'VERIFY_METHOD_CONFIG_NOT_EXISTS' => '10013',
        'VERIFY_CONTROLLER_NOT_EXISTS'    => '10014',
        'VERIFY_METHOD_NOT_EXISTS'        => '10015',
        'BLOOM_FILTER_KEY_REQUIRE'        => '10016',
        'BLOOM_FILTER_FUNC_REQUIRE'       => '10017',
        'BLOOM_FILTER_FUNC_TOO_FEW'       => '10018',
        'TOKEN_BUCKET_KEY_REQUIRE'        => '10019',
        'CLASS_NOT_EXISTS'                => '10020',
    ),
);