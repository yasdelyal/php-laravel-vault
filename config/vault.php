<?php

return [
    'vars' => [
        /*
         * Patches for Driver
         * Envs from all patches will be merged
         * Can be used variables like {app} - for this, put it into patch_variables array
         * Examples:
         * /secret/{app}/{env}
         * /secret/{app}/common
         */
        'patches' => [
            //
        ],

        /*
         * Variables for patches
         * They will be substituted at render time
         */
        'patch_variables' => [
            'env' => config('app.env'),
            'app' => config('app.name')
        ],
        /*
         * Variables validation before save in .next
         * By default, it get .env.example from default folder
         * But this can be overwritten with example_file_patch and example_file_name
         * strict:
         * true - received envs must be equal with example (order does not count)
         * false - received envs must has all keys from example or more
         */
        'validation' => [
            'enabled'       => ENV('VAULT_VARS_VALIDATION_ENABLED', true),
            'strict'        => ENV('VAULT_VARS_VALIDATION_STRICT', false),
            'example_file_patch' => ENV('VAULT_VARS_VALIDATION_EXAMPLE_PATCH'),
            'example_file_name'  => ENV('VAULT_VARS_VALIDATION_EXAMPLE_NAME'),
        ]
    ],
    'default_connection' => env('VAULT_DEFAULT_CONNECTION', 'vault'),
    'connections' => [
        'vault' => [
            'driver'    => 'hashicorp_vault_v1',
            'host'      => ENV('VAULT_HOST', '127.0.0.1'),
            'port'      => ENV('VAULT_PORT', 8200),
            'role_id'   => ENV('VAULT_ROLE_ID', 'role_id'),
            'secret_id' => ENV('VAULT_SECRET_ID', 'secret_id'),
            'role_name' => ENV('VAULT_ROLE_NAME', 'approle'),
        ]
    ],
];
