<?php
    /**
     * Portal configuration file for UserFrosting.
     *
     */
    return [
        'address_book' => [
            'admin' => [
                'name'  => 'DO!Hack Team'
            ]
        ],
        'site' => [
            'author' => 'DO!Hack',
            'title' => 'DO!Hack',
            'analytics' => [
                'google' => [
                    'enabled' => false
                ]
            ],
            'registration' => [
                'user_defaults' => [
                    'locale' => 'en_US',
                    'group' => 'user'
                ]
            ],
            'uri' => [
                'author' => '//portal.dohack.io',
                'publisher' => '//portal.dohack.io',
                'cli' => 'https://portal.dohack.io'
            ]
        ],
        'php' => [
            'timezone' => 'Europe/Berlin',
        ],
        'event' => [
            'year' => 2017,
            'deadline' => '30.09.2017'
        ],
        'swot' => [
            'domains_path' => '../swot/lib/domains'
        ]
    ];
