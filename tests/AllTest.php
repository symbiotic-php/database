<?php

namespace Symbiotic\Tests\Database;

use Symbiotic\Database\DatabaseManager;

class AllTest extends \PHPUnit\Framework\TestCase
{

    private array $config = [
        'default' => 'mysql',
        'connections' => [
            'mysql' => [
                'driver' => 'mysql',
                'database' => 'database',
                'username' => 'root',
                'password' => 'toor',
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
            ],
            'mysql_dev' => [
                'driver' => 'mysql',
                'database' => 'database_dev',
                'username' => 'root',
                'password' => 'toor',
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
            ],
            'app2' => [
                'driver' => 'mysql',
                'database' => 'app1',
                'username' => 'root',
                'password' => 'toor',
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
            ],
            'new_connect' => [
                'driver' => 'mysql',
                'database' => 'new_connect',
                'username' => 'root',
                'password' => 'toor',
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
            ]
        ],
        'namespaces' => [
            __NAMESPACE__ . '\\NotExists\\' => '',
            __NAMESPACE__ . '\\NotExists\\Two' => '',
            __NAMESPACE__ . '\\Models\\App1' => 'mysql_dev',
            __NAMESPACE__ . '\\Models\\App2' => 'app2'
        ]
    ];

    /**
     * @covers \Symbiotic\Database\DatabaseManager::fromArray
     * @covers \Symbiotic\Database\DatabaseManager::getConnections
     * @covers \Symbiotic\Database\DatabaseManager::getDefaultConnectionName
     *
     * @return void
     */
    public function testFromArray(): void
    {
        $db = DatabaseManager::fromArray($this->config);
        $this->assertEquals($this->config['default'], $db->getDefaultConnectionName());
        $this->assertEquals($this->config['connections'], $db->getConnections());
        $this->assertTrue($db->hasConnection('mysql'));
        $this->assertEquals(
            $this->config['namespaces'][__NAMESPACE__ . '\\Models\\App2'],
            $db->getNamespaceConnection(__NAMESPACE__ . '\\Models\\App2')
        );
    }

    /**
     * @covers \Symbiotic\Database\ConnectionsConfig::addConnection
     * @covers \Symbiotic\Database\ConnectionsConfig::getConnection
     * @covers \Symbiotic\Database\ConnectionsConfig::hasConnection
     * @covers \Symbiotic\Database\ConnectionsConfig::removeConnection
     * @covers \Symbiotic\Database\ConnectionsConfig::setDefault
     * @return void
     */
    public function testConnections(): void
    {
        $db = DatabaseManager::fromArray($this->config);

        $this->assertEquals($this->config['connections']['new_connect'], $db->getConnection('new_connect'));
        $new = [
            'driver' => 'mysql',
            'database' => 'my',
            'username' => 'root',
            'password' => 'toor',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
        ];
        $db->addConnection($new, 'new');
        $this->assertEquals($new, $db->getConnection('new'));

        $db->setDefault('new');
        $this->assertSame('new', $db->getDefaultConnectionName());

        $db->removeConnection('new');
        $this->assertFalse($db->hasConnection('new'));
        $this->assertNull($db->getConnection('new'));
    }

    /**
     * @covers \Symbiotic\Database\DatabaseManager::addNamespaceConnection
     * @covers \Symbiotic\Database\DatabaseManager::getNamespaceConnection
     * @covers \Symbiotic\Database\DatabaseManager::findNamespaceConnectionName
     * @covers \Symbiotic\Database\DatabaseManager::__toString
     * @covers \Symbiotic\Database\DatabaseManager::activateNamespacesConnectionsFinder
     * @return void
     */
    public function testNamespaceFinder(): void
    {
        $db = DatabaseManager::fromArray($this->config);
        $db->addNamespaceConnection(__NAMESPACE__, 'new_connect');

        $this->assertSame('new_connect', $db->getNamespaceConnection(__NAMESPACE__));
        $this->assertSame($this->config['connections']['new_connect'], $db->getConnection(__NAMESPACE__));

        $this->assertSame('new_connect', (string)$db);
        // выключаем
        $db->activateNamespaceFinder(false);

        $this->assertNull($db->getNamespaceConnection(__NAMESPACE__));
        $this->assertNull($db->findNamespaceConnectionName());
        $this->assertSame($db->getDefaultConnectionName(), (string)$db);

        // включаем
        $db->activateNamespaceFinder(true);
        $this->assertEquals(
            $this->config['namespaces'][__NAMESPACE__ . '\\Models\\App2'],
            $db->getNamespaceConnection(__NAMESPACE__ . '\\Models\\App2')
        );
    }
}
