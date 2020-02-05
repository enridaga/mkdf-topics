<?php

namespace MKDF\Topics;

use Zend\ServiceManager\Factory\InvokableFactory;
use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;

return [
    'controllers' => [
        'factories' => [
            Controller\CollectionController::class => Controller\Factory\CollectionControllerFactory::class
        ],
    ],
    'service_manager' => [
        'aliases' => [
            Repository\MKDFTopicsRepositoryInterface::class => Repository\MKDFTopicsRepository::class
        ],
        'factories' => [
            Repository\MKDFTopicsRepository::class => Repository\Factory\MKDFTopicsRepositoryFactory::class
        ]
    ],
    'router' => [
        'routes' => [
            'collection' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/collection[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z0-9_-]*',
                    ],
                    'defaults' => [
                        'controller' => Controller\CollectionController::class,
                        'action' => 'index'
                    ],
                ],
            ],
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            'topics' => __DIR__ . '/../view',
        ],
    ],
    'controller_plugins' => [
        'factories' => [
            Controller\Plugin\DatahubTopicsRepositoryPlugin::class => Controller\Plugin\Factory\DatahubTopicsRepositoryPluginFactory::class,
        ],
        'aliases' => [
            'datahubTopicsRepository' => Controller\Plugin\DatahubTopicsRepositoryPlugin::class,
        ]
    ],
    // The 'access_filter' key is used by the User module to restrict or permit
    // access to certain controller actions for unauthenticated visitors.
    'access_filter' => [
        'options' => [
            // The access filter can work in 'restrictive' (recommended) or 'permissive'
            // mode. In restrictive mode all controller actions must be explicitly listed
            // under the 'access_filter' config key, and access is denied to any not listed
            // action for users not logged in. In permissive mode, if an action is not listed
            // under the 'access_filter' key, access to it is permitted to anyone (even for
            // users not logged in. Restrictive mode is more secure and recommended.
            'mode' => 'restrictive'
        ],
        'controllers' => [
            Controller\CollectionController::class => [
                // Allow anyone to visit "index" and "about" actions
                ['actions' => ['index'], 'allow' => '*'],
                ['actions' => ['details'], 'allow' => '@'],
                // Allow authenticated users to ...
                ['actions' => ['add','edit','delete','delete-confirm'], 'allow' => '@']
            ],
        ]
    ],
    'navigation' => [
        'default' => [
            [
                'label' => 'Collections',
                'route' => 'collection',
            ],
        ],
    ],
];
