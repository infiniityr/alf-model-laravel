<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Alfresco configuration
    |--------------------------------------------------------------------------
    |
    | Configuration of Alfresco
    |
    */
    'config' => [
        'url' => env('ALFURL', 'http://localhost'),
        'port' => env('ALFPORT', '8000'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Alfresco Routes
    |--------------------------------------------------------------------------
    |
    | This array of routes will provide the application with easy loaded routes.
    |
    |
    */
    'routes' => [
        'login' => [
            'url' => '/alfresco/s/api/login',
            'method' => 'post',
        ],
        'check_login' => [
            'url' => '/alfresco/s/api/login/ticket/',
            'method' => 'get',
        ],
        'content_repository_node' => [
            'url' => '/alfresco/s/slingshot/doclib2/doclist/type/node/store_type/store_id/id/path',
            'method' => 'get',
            'url_config' => [
                'type' => 'all',
                'store_type',
                'store_id',
                'id',
                'path' => '',
            ]
        ],
        'content_repository_path' => [
            'url' => '/alfresco/s/slingshot/doclib2/doclist/type/site/site2/container/path',
            'method' => 'get',
            'url_config' => [
                'type' => 'all',
                'site2',
                'container' => 'documentLibrary',
                'path' => '',
            ]
        ],
        'file_metadata' => [
            'url' => '/alfresco/s/api/metadata',
            'method' => 'get',
        ],
        'file_information' => [
            'url' => '/alfresco/s/slingshot/doclib2/doclist/type/node/store_type/store_id/id',
            'method' => 'get',
            'url_config' => [
                'type' => 'all',
                'store_type',
                'store_id',
                'id',
            ]
        ],
        'search' => [
            'url' => '/alfresco/s/slingshot/search',
            'method' => 'get',
        ],
        'file_user' => [
            'url' => '/alfresco/s/slingshot/doclib2/doclist/type/node/alfresco/user/home/path',
            'method' => 'get',
            'url_config' => [
                'type' => 'all',
                'path' => '',
            ]
        ],
        'user_preferences' => [
            'url' => '/alfresco/s/api/people/login/preferences',
            'method' => 'get',
            'url_config' => [
                'login'
            ]
        ],

        //All route added by the developer


    ],

    /*
    |--------------------------------------------------------------------------
    | Class Macros
    |--------------------------------------------------------------------------
    |
    | This array of Macros will help the developer when he would like to call
    | a custom attribute.
    | For example : the macro 'nodeRef' will search for node.nodeRef and metadata.parent.nodeRef reference.
    |
    */
    'macros' => [
        \Infiniityr\Alfresco\Alfresco::class => [

        ],
        \Infiniityr\Alfresco\AlfrescoFile::class => [
            'nodeRef' => [
                'node.nodeRef',
                'metadata.parent.nodeRef',

            ],
            'repoPath' => 'location.repoPath',
            'canWrite' => [
                'node.permissions.user.Write',
                'metadata.parent.permissions.user.Write',
            ],
            'canDelete' => [
                'node.permissions.user.Delete',
                'metadata.parent.permissions.user.Delete',
            ],
            'isLocked' => [
                'node.isLocked',
                'metadata.parent.isLocked',
            ],
            'creator' => 'node.properties.cm:creator',
            'name' => 'node.properties.cm:name',
            'permissions' => [
                'metadata.parent.permissions.user',
                'node.permissions.user',
            ]
        ],
        \Infiniityr\Alfresco\AlfrescoDirectory::class => [
            'nodeRef' => [
                'node.nodeRef',
                'metadata.parent.nodeRef'
            ],
            'name' => [
                'metadata.parent.properties.cm:name',
                'node.properties.cm:name'
            ]
        ],
        \Infiniityr\Alfresco\AlfrescoSearch::class => [

        ],

    ]
];