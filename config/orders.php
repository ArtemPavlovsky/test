<?php

return [

    'columns' => [
        'csv' => [
            'Unique Transaction ID', 'eBay Total Sale Amount', 'Click Timestamp',
        ],
        'xml' => [
            'advcampaign_id', 'order_id', 'status', 'cart', 'currency', 'action_date',
        ],
    ],
    'additional_filter' => [
        'csv' => ['Event Type' => 'Winning Bid (Revenue)'],
        'xml' => false,
    ],
    'wrapper' => [
        'csv' => false,
        'xml' => 'stat',
    ],
    'static_fields' => [
        'csv' => [
            'advcampaign_id' => 1,
            'status' => 'approved',
            'currency' => 'USD'
        ],
        'xml' => [],
    ],
    'fields_names' => [
        'csv' => [
            'action_date' => 'Click Timestamp',
            'order_id' => 'Unique Transaction ID',
            'cart' => 'eBay Total Sale Amount',
            'advcampaign_id' => 'advcampaign_id',
            'status' => 'status',
            'currency' => 'currency',
        ],
        'xml' => [
            'action_date' => 'action_date',
            'order_id' => 'order_id',
            'cart' => 'cart',
            'advcampaign_id' => 'advcampaign_id',
            'status' => 'status',
            'currency' => 'currency',
        ],
    ],
];