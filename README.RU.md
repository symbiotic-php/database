# Symbiotic Database

**Пакет конфигурации подключений к базам данных с возможностью выбора подключения в зависимости от пространства имен.**

## Установка

```
composer require symbiotic/database
```

## Описание

Пакет содержит два основных интерфейса и менеджер:

- `ConnectionsConfigInterface`  - Отвечает за хранение подключений
- `NamespaceConnectionsConfigInterface`  - Отвечает за хранение подключений пространств имен
- `DatabaseManager` - Менеджер, содержит все два интерфейса, \ArrayAccess , \Stringable

### Использование

Инициализация подключений:

```php
    $config = [
       'default' => 'my_connect_name',
        // Подключения по пакетам
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
    
  // Постройка из массива
  $manager = \Symbiotic\Database\DatabaseManager::fromArray($config);
  
  // Постройка через конструктор
  $manager = new \Symbiotic\Database\DatabaseManager(
            new \Symbiotic\Database\ConnectionsConfig($config['connections'], $config['default']),
            new \Symbiotic\Database\NamespaceConnectionsConfig($config['namespaces']) // необязательно
        );
```

Методы `ConnectionsConfigInterface` и `\ArrayAccess`:

```php
/**
* @var \Symbiotic\Database\DatabaseManager $manager 
 */
// Получение всех подключений
$connections = $manager->getConnections();

// Подключение по умолчанию
$defaultConnection = $manager->getDefaultConnectionName();

// Проверка наличия конфига подключения
$bool = $manager->hasConnection('my_connect_name');
$bool = isset($manager['my_connect_name']);
 
// Получение данных подключения
$connectionData = $manager->getConnection('my_connect_name');
$connectionData = $manager['my_connect_name'];

// Получение данных подключения по пространству имен, если включен поисковик по неймспейсам (ниже описание)
$connectionData = $manager->getConnection(\Modules\PagesApplication\Models\Event::class);
 
// Добавление подключения
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

// Удаление подключения по имени
$manager->removeConnection('test_connection');
unset($manager['test_connection']);


```

Методы `NamespaceConnectionsConfigInterface`:

```php
/**
 * @var \Symbiotic\Database\DatabaseManager $manager 
 */

// Активен ли поиск подключения по неймспейсу
$bool = $manager->isActiveNamespaceFinder();

// Включение/ выключение авто-поиска
$manager->activateNamespaceFinder(false);

// Добавление подключения для модуля
$manager->addNamespaceConnection('\\Modules\\PagesApplication', 'test_connection');

// Получение названия подключения по классу, если выключен вернет null
$pagesConnectionName = $manager->getNamespaceConnection(\Modules\PagesApplication\Models\Event::class); // вернет `test_connection`

// Автоматический поиск подключения по стеку вызова, если выключен вернет null
$connectionData = $manager->findNamespaceConnectionName();


```

## Особенности поведения

Дополнительно есть умный метод __toString(), если включен поиск по неймспейсам `isActiveNamespaceFinder()`,
он ищет подключение по пространству имен через метод `findNamespaceConnectionName()`
или возвращает подключение по умолчанию из метода `getDefaultConnectionName()`

#### Также обратите внимание на поведение при отключенном определении подключений по неймспейсам!

Примеры:

```php
// Часть конфига
'default' => 'my_connect_name',
// Подключения по пакетам
'namespaces' => [
   '\\Modules\\Articles' => 'mysql_dev',
]
/**
 * @var \Symbiotic\Database\DatabaseManager $manager 
 */
 // Неймспейс из конфига 
namespace  Modules\Articles\Models {

$objectConnectionName = (string)$manager; //  mysql_dev (подключение неймспейса)
$objectConnectionData = $manager->getConnection(__NAMESPACE__); //  mysql_dev config  (подключение неймспейса)
$objectConnectionName = $manager->getNamespaceConnection(__NAMESPACE__); //  mysql_dev  (подключение неймспейса)
$connectionData = $manager->findNamespaceConnectionName(); //  mysql_dev  (подключение неймспейса)

// выключаем определение по неймспейсам
$manager->activateNamespaceFinder(false);

$objectConnectionName = (string)$manager; //  my_connect_name (подключение по умолчанию)
$objectConnectionData = $manager->getConnection(__NAMESPACE__); //  NULL
$objectConnectionName = $manager->findNamespaceConnectionName();//  NULL
$objectConnectionName = $manager->getNamespaceConnection(__NAMESPACE__); // NULL
// Подключение по неймспейсу можно запросить напрямую из конфига
$objectConnectionName = $manager->getNamespacesConfig()->getNamespaceConnection(__NAMESPACE__); // mysql_dev (подключение неймспейса)

}
// Любой другой неймспейс
namespace  Modules\NewSpace\Models {

$objectConnectionName = (string)$manager; //  my_connect_name  (подключение по умолчанию)
$objectConnectionData = $manager->getConnection(__NAMESPACE__); //  NULL
$objectConnectionName = $manager->findNamespaceConnectionName();//  NULL
$objectConnectionName = $manager->getNamespaceConnection(__NAMESPACE__); // NULL
}

```

## Для Symbiotic приложений

Для приложений фреймворка Symbiotic есть провайдер для автоматической установки подключения из настроек приложения
относительно базового немспейса пакета

1. Чтобы добавить поле выбора базы данных, создайте поле c именем `database_connection_name` в полях настроек пакета:

```json
// symbiotic.json
{
  "settings_fields": [
    {
      "label": "База данных",
      "name": "database_connection_name",
      "type": "settings::database"
    }
    /// Другие поля настроек...
  ]
}

```

2. В секции приложения `"app"` добавьте провайдер \Symbiotic\Database\AppNamespaceConnectionProvider

```json
// symbiotic.json
{
  "app": {
    "id": "my_app",
    //....
    "providers": [
      "\\Symbiotic\\Database\\AppNamespaceConnectionProvider" // Добавленный провайдер
    ]
    /// Другие настройки приложения...
  }
}

```
