#!/usr/bin/env php
<?php

/**
 * Laravel Filament MCP Server
 * A simple MCP server for Laravel Filament development
 */

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

class LaravelMcpServer
{
    private $app;
    private $resources = [];
    private $tools = [];

    public function __construct()
    {
        $this->initializeLaravel();
        $this->registerResources();
        $this->registerTools();
    }

    private function initializeLaravel()
    {
        $this->app = require __DIR__ . '/bootstrap/app.php';
        $kernel = $this->app->make(Illuminate\Contracts\Console\Kernel::class);
        $kernel->bootstrap();
    }

    private function registerResources()
    {
        $this->resources = [
            'laravel-routes' => [
                'uri' => 'laravel://routes',
                'name' => 'Laravel Routes',
                'description' => 'List all Laravel routes',
                'mimeType' => 'application/json'
            ],
            'laravel-models' => [
                'uri' => 'laravel://models',
                'name' => 'Laravel Models',
                'description' => 'List all Eloquent models',
                'mimeType' => 'application/json'
            ],
            'laravel-migrations' => [
                'uri' => 'laravel://migrations',
                'name' => 'Database Migrations',
                'description' => 'List database migrations',
                'mimeType' => 'application/json'
            ],
            'laravel-filament-resources' => [
                'uri' => 'laravel://filament-resources',
                'name' => 'Filament Resources',
                'description' => 'List all Filament resources',
                'mimeType' => 'application/json'
            ],
            'laravel-filament-pages' => [
                'uri' => 'laravel://filament-pages',
                'name' => 'Filament Pages',
                'description' => 'List all Filament pages',
                'mimeType' => 'application/json'
            ],
        ];
    }

    private function registerTools()
    {
        $this->tools = [
            'laravel-make-model' => [
                'name' => 'laravel-make-model',
                'description' => 'Create a new Laravel model',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'name' => ['type' => 'string', 'description' => 'Model name'],
                        'migration' => ['type' => 'boolean', 'description' => 'Create migration', 'default' => true],
                        'factory' => ['type' => 'boolean', 'description' => 'Create factory', 'default' => false],
                        'seed' => ['type' => 'boolean', 'description' => 'Create seeder', 'default' => false]
                    ],
                    'required' => ['name']
                ]
            ],
            'laravel-make-migration' => [
                'name' => 'laravel-make-migration',
                'description' => 'Create a new Laravel migration',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'name' => ['type' => 'string', 'description' => 'Migration name'],
                        'table' => ['type' => 'string', 'description' => 'Table name'],
                        'create' => ['type' => 'boolean', 'description' => 'Create new table', 'default' => false]
                    ],
                    'required' => ['name']
                ]
            ],
            'laravel-make-filament-resource' => [
                'name' => 'laravel-make-filament-resource',
                'description' => 'Create a new Filament resource',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'name' => ['type' => 'string', 'description' => 'Resource name'],
                        'generate' => ['type' => 'boolean', 'description' => 'Generate with scaffolding', 'default' => true],
                        'soft-deletes' => ['type' => 'boolean', 'description' => 'Include soft deletes', 'default' => false]
                    ],
                    'required' => ['name']
                ]
            ],
            'laravel-run-migration' => [
                'name' => 'laravel-run-migration',
                'description' => 'Run Laravel migrations',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'step' => ['type' => 'boolean', 'description' => 'Run migrations one by one', 'default' => false],
                        'seed' => ['type' => 'boolean', 'description' => 'Run seeders after migration', 'default' => false]
                    ]
                ]
            ],
            'laravel-clear-cache' => [
                'name' => 'laravel-clear-cache',
                'description' => 'Clear Laravel caches',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'config' => ['type' => 'boolean', 'description' => 'Clear config cache', 'default' => true],
                        'route' => ['type' => 'boolean', 'description' => 'Clear route cache', 'default' => true],
                        'view' => ['type' => 'boolean', 'description' => 'Clear view cache', 'default' => true],
                        'cache' => ['type' => 'boolean', 'description' => 'Clear application cache', 'default' => true]
                    ]
                ]
            ]
        ];
    }

    public function handleRequest($request)
    {
        $method = $request['method'] ?? '';
        $params = $request['params'] ?? [];

        switch ($method) {
            case 'initialize':
                return $this->initialize();
            case 'resources/list':
                return $this->listResources();
            case 'resources/read':
                return $this->readResource($params);
            case 'tools/list':
                return $this->listTools();
            case 'tools/call':
                return $this->callTool($params);
            default:
                return ['error' => 'Unknown method'];
        }
    }

    private function initialize()
    {
        return [
            'protocolVersion' => '2024-11-05',
            'capabilities' => [
                'resources' => [
                    'subscribe' => false,
                    'listChanged' => false
                ],
                'tools' => [
                    'listChanged' => false
                ]
            ],
            'serverInfo' => [
                'name' => 'Laravel Filament MCP Server',
                'version' => '1.0.0'
            ]
        ];
    }

    private function listResources()
    {
        return ['resources' => array_values($this->resources)];
    }

    private function readResource($params)
    {
        $uri = $params['uri'] ?? '';

        switch ($uri) {
            case 'laravel://routes':
                return $this->getRoutes();
            case 'laravel://models':
                return $this->getModels();
            case 'laravel://migrations':
                return $this->getMigrations();
            case 'laravel://filament-resources':
                return $this->getFilamentResources();
            case 'laravel://filament-pages':
                return $this->getFilamentPages();
            default:
                return ['error' => 'Unknown resource'];
        }
    }

    private function getRoutes()
    {
        $routes = [];
        $routeCollection = \Illuminate\Support\Facades\Route::getRoutes();

        foreach ($routeCollection as $route) {
            $routes[] = [
                'uri' => $route->uri(),
                'methods' => $route->methods(),
                'name' => $route->getName(),
                'action' => $route->getActionName(),
                'middleware' => $route->middleware(),
                'prefix' => $route->getPrefix()
            ];
        }

        return [
            'contents' => [
                'type' => 'text',
                'text' => json_encode($routes, JSON_PRETTY_PRINT)
            ]
        ];
    }

    private function getModels()
    {
        $models = [];
        $modelPath = app_path('Models');

        if (is_dir($modelPath)) {
            $files = glob($modelPath . '/*.php');
            foreach ($files as $file) {
                $className = 'App\\Models\\' . pathinfo($file, PATHINFO_FILENAME);
                if (class_exists($className)) {
                    $models[] = [
                        'name' => $className,
                        'file' => $file,
                        'table' => method_exists($className, 'getTable') ? (new $className())->getTable() : null
                    ];
                }
            }
        }

        return [
            'contents' => [
                'type' => 'text',
                'text' => json_encode($models, JSON_PRETTY_PRINT)
            ]
        ];
    }

    private function getMigrations()
    {
        $migrations = [];
        $migrationPath = database_path('migrations');

        if (is_dir($migrationPath)) {
            $files = glob($migrationPath . '/*.php');
            foreach ($files as $file) {
                $migrations[] = [
                    'file' => basename($file),
                    'path' => $file,
                    'batch' => $this->getMigrationBatch(basename($file))
                ];
            }
        }

        return [
            'contents' => [
                'type' => 'text',
                'text' => json_encode($migrations, JSON_PRETTY_PRINT)
            ]
        ];
    }

    private function getFilamentResources()
    {
        $resources = [];
        $resourcePath = app_path('Filament/Resources');

        if (is_dir($resourcePath)) {
            $files = glob($resourcePath . '/*.php');
            foreach ($files as $file) {
                $resources[] = [
                    'name' => pathinfo($file, PATHINFO_FILENAME),
                    'file' => $file,
                    'path' => str_replace(app_path(), 'app', $file)
                ];
            }
        }

        return [
            'contents' => [
                'type' => 'text',
                'text' => json_encode($resources, JSON_PRETTY_PRINT)
            ]
        ];
    }

    private function getFilamentPages()
    {
        $pages = [];
        $pagePath = app_path('Filament/Pages');

        if (is_dir($pagePath)) {
            $files = glob($pagePath . '/*.php');
            foreach ($files as $file) {
                $pages[] = [
                    'name' => pathinfo($file, PATHINFO_FILENAME),
                    'file' => $file,
                    'path' => str_replace(app_path(), 'app', $file)
                ];
            }
        }

        return [
            'contents' => [
                'type' => 'text',
                'text' => json_encode($pages, JSON_PRETTY_PRINT)
            ]
        ];
    }

    private function listTools()
    {
        return ['tools' => array_values($this->tools)];
    }

    private function callTool($params)
    {
        $name = $params['name'] ?? '';
        $arguments = $params['arguments'] ?? [];

        switch ($name) {
            case 'laravel-make-model':
                return $this->makeModel($arguments);
            case 'laravel-make-migration':
                return $this->makeMigration($arguments);
            case 'laravel-make-filament-resource':
                return $this->makeFilamentResource($arguments);
            case 'laravel-run-migration':
                return $this->runMigration($arguments);
            case 'laravel-clear-cache':
                return $this->clearCache($arguments);
            default:
                return ['error' => 'Unknown tool'];
        }
    }

    private function makeModel($arguments)
    {
        $name = $arguments['name'] ?? '';
        $migration = $arguments['migration'] ?? true;
        $factory = $arguments['factory'] ?? false;
        $seed = $arguments['seed'] ?? false;

        if (empty($name)) {
            return ['error' => 'Model name is required'];
        }

        $command = "make:model {$name}";
        if ($migration) $command .= " --migration";
        if ($factory) $command .= " --factory";
        if ($seed) $command .= " --seed";

        $result = $this->runArtisan($command);

        return [
            'content' => [
                'type' => 'text',
                'text' => $result
            ]
        ];
    }

    private function makeMigration($arguments)
    {
        $name = $arguments['name'] ?? '';
        $table = $arguments['table'] ?? null;
        $create = $arguments['create'] ?? false;

        if (empty($name)) {
            return ['error' => 'Migration name is required'];
        }

        $command = "make:migration {$name}";
        if ($table) $command .= " --table={$table}";
        if ($create) $command .= " --create";

        $result = $this->runArtisan($command);

        return [
            'content' => [
                'type' => 'text',
                'text' => $result
            ]
        ];
    }

    private function makeFilamentResource($arguments)
    {
        $name = $arguments['name'] ?? '';
        $generate = $arguments['generate'] ?? true;
        $softDeletes = $arguments['soft-deletes'] ?? false;

        if (empty($name)) {
            return ['error' => 'Resource name is required'];
        }

        $command = "make:filament-resource {$name}";
        if ($generate) $command .= " --generate";
        if ($softDeletes) $command .= " --soft-deletes";

        $result = $this->runArtisan($command);

        return [
            'content' => [
                'type' => 'text',
                'text' => $result
            ]
        ];
    }

    private function runMigration($arguments)
    {
        $step = $arguments['step'] ?? false;
        $seed = $arguments['seed'] ?? false;

        $command = "migrate";
        if ($step) $command .= " --step";
        if ($seed) $command .= " --seed";

        $result = $this->runArtisan($command);

        return [
            'content' => [
                'type' => 'text',
                'text' => $result
            ]
        ];
    }

    private function clearCache($arguments)
    {
        $config = $arguments['config'] ?? true;
        $route = $arguments['route'] ?? true;
        $view = $arguments['view'] ?? true;
        $cache = $arguments['cache'] ?? true;

        $result = '';
        if ($config) $result .= $this->runArtisan("config:clear");
        if ($route) $result .= $this->runArtisan("route:clear");
        if ($view) $result .= $this->runArtisan("view:clear");
        if ($cache) $result .= $this->runArtisan("cache:clear");

        return [
            'content' => [
                'type' => 'text',
                'text' => $result
            ]
        ];
    }

    private function runArtisan($command)
    {
        $artisan = base_path('artisan');
        $output = [];
        exec("php {$artisan} {$command} 2>&1", $output, $returnCode);

        return implode("\n", $output) . "\nReturn code: {$returnCode}";
    }

    private function getMigrationBatch($filename)
    {
        try {
            $migration = \DB::table('migrations')->where('migration', str_replace('.php', '', $filename))->first();
            return $migration->batch ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function run()
    {
        while ($line = fgets(STDIN)) {
            $request = json_decode($line, true);
            $response = $this->handleRequest($request);
            fwrite(STDOUT, json_encode($response) . "\n");
        }
    }
}

// Run the server
$server = new LaravelMcpServer();
$server->run();