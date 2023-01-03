<?php

return [
    'name' => 'Loan',

    'permissions' => [
        'loan_create' => [
            'customer'
        ],
        'loan_approve' => [
            'admin'
        ],
        'loan_list' => [
            'admin',
            'customer'
        ],
        'loan_view' => [
            'admin',
            'customer'
        ],
        'loan_repay' => [
            'customer'
        ]
    ]
];
