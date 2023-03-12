# Symbiotic Database
README.RU.md  [РУССКОЕ ОПИСАНИЕ](https://github.com/symbiotic-php/database/blob/master/README.RU.md)

**Database connection configuration package with the ability to select a connection depending on the namespace.**

## Installing

```
composer require symbiotic/database
```

## Description

The package contains two main interfaces and a manager:

- `ConnectionsConfigInterface` - Responsible for storing connections
- `NamespaceConnectionsConfigInterface` - Responsible for storing namespace connections
- `DatabaseManager` - Manager, contains all two interfaces, \ArrayAccess , \Stringable
- 
### Usage

Initializing Connections:

```php
    $config = [
       'default' => 'my_connect_name',
        // Namespace connections
        'namespaces' => [
           '\\Modules\\Articles' => 'mysql_dev',
        ]
       'connections' => [
            'my_connect_name' => [
                'driver' => 'mysql',
                'database' => 'database',
                'username' => 'root',
                'password' => 'toor',
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
            ],
            'mysql_dev' => [
             // ....
            ],
        ]
    ];
    
  // Building from an array
  $manager = \Symbiotic\Database\DatabaseManager::fromArray($config);
  
  // Building via constructor
  $manager = new \Symbiotic\Database\DatabaseManager(
            new \Symbiotic\Database\ConnectionsConfig($config['connections'], $config['default']),
            new \Symbiotic\Database\NamespaceConnectionsConfig($config['namespaces']) // необязательно
        );
```

Methods `ConnectionsConfigInterface` и `\ArrayAccess`:

```php
/**
 * @var \Symbiotic\Database\DatabaseManager $manager 
 */
// Getting all connections
$connections = $manager->getConnections();

// Default Connection
$defaultConnection = $manager->getDefaultConnectionName();

// Checking if a connection config exists
$bool = $manager->hasConnection('my_connect_name');
$bool = isset($manager['my_connect_name']);
 
// Getting connection data
$connectionData = $manager->getConnection('my_connect_name');
$connectionData = $manager['my_connect_name'];

// Retrieving connection data by namespace, if search engine by namespaces is enabled (description below)
$connectionData = $manager->getConnection(\Modules\PagesApplication\Models\Event::class);
 
// Adding a connection
$manager->addConnection(
         [
            'driver' => 'mysql',
            'database' => 'test_db',
            'username' => 'root',
            'password' => 'toor',
            //....
        ],
        'test_connection'
);
$manager['my_connect_name'] = [
//....
];

// Deleting a connection by name
$manager->removeConnection('test_connection');
unset($manager['test_connection']);


```

Methods `NamespaceConnectionsConfigInterface`:

```php
/**
 * @var \Symbiotic\Database\DatabaseManager $manager 
 */

// Is the connection search by namespace active?
$bool = $manager->isActiveNamespaceFinder();

// Enable/disable search
$manager->activateNamespaceFinder(false);

// Adding a connection for a module
$manager->addNamespaceConnection('\\Modules\\PagesApplication', 'test_connection');

// Getting the name of the connection by class, if disabled, it will return null
$pagesConnectionName = $manager->getNamespaceConnection(\Modules\PagesApplication\Models\Event::class); // return `test_connection`

// Automatic connection search in the call stack, if disabled, returns null
$connectionData = $manager->findNamespaceConnectionName();


```

## Behavioral Features

Additionally, there is a smart __toString() method. If namespace search is enabled `isActiveNamespaceFinder()`,
it looks for a connection by namespace via the `findNamespaceConnectionName()` method
or returns the default connection from the `getDefaultConnectionName()` method

#### Also pay attention to the behavior when the definition of connections by namespaces is disabled!

Examples:

```php
// Configuration part
'default' => 'my_connect_name',
// Packet Connections
'namespaces' => [
   '\\Modules\\Articles' => 'mysql_dev',
]
/**
 * @var \Symbiotic\Database\DatabaseManager $manager 
 */
 // Installed Namespace from config 
namespace  Modules\Articles\Models {

$objectConnectionName = (string)$manager; //  mysql_dev (namespace connection)
$objectConnectionData = $manager->getConnection(__NAMESPACE__); //  mysql_dev config  (namespace connection)
$objectConnectionName = $manager->getNamespaceConnection(__NAMESPACE__); //  mysql_dev  (namespace connection)
$connectionData = $manager->findNamespaceConnectionName(); //  mysql_dev  (namespace connection)

// turn off detection by namespaces
$manager->activateNamespaceFinder(false);

$objectConnectionName = (string)$manager; //  my_connect_name (default)
$objectConnectionData = $manager->getConnection(__NAMESPACE__); //  NULL
$objectConnectionName = $manager->findNamespaceConnectionName();//  NULL
$objectConnectionName = $manager->getNamespaceConnection(__NAMESPACE__); // NULL
// Namespace connection can be requested directly from the config
$objectConnectionName = $manager->getNamespacesConfig()->getNamespaceConnection(__NAMESPACE__); // mysql_dev (namespace connection)

}
// Любой другой неймспейс
namespace  Modules\NewSpace\Models {

$objectConnectionName = (string)$manager; //  my_connect_name  (default)
$objectConnectionData = $manager->getConnection(__NAMESPACE__); //  NULL
$objectConnectionName = $manager->findNamespaceConnectionName();//  NULL
$objectConnectionName = $manager->getNamespaceConnection(__NAMESPACE__); // NULL
}

```

## For Symbiotic Applications

Symbiotic framework applications have a provider to automatically establish a connection from the application settings
relative to the base nemspace of the package.

1. To add a database selection field, create a field named `database_connection_name` in the package settings fields:

```json
// symbiotic.json
{
  "settings_fields": [
    {
      "label": "App Database",
      "name": "database_connection_name",
      "type": "settings::database"
    }
    /// Other settings fields...
  ]
}

```

2. In the application section `"app"` add the provider \Symbiotic\Database\AppNamespaceConnectionProvider

```json
// symbiotic.json
{
  "app": {
    "id": "my_app",
    //....
    "providers": [
      "\\Symbiotic\\Database\\AppNamespaceConnectionProvider" // Added provider
    ]
    /// More app settings...
  }
}

```
