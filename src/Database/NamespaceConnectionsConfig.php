<?php

declare(strict_types=1);

namespace Symbiotic\Database;

class NamespaceConnectionsConfig implements NamespaceConnectionsConfigInterface
{
    protected array $namespacesConnections = [];


    /**
     * Высота автоматического поиска подключения неймспейса через debug_backtrace
     *
     * @see DatabaseManager::findNamespaceConnectionName()
     *
     * @var int
     */
    protected int $namespaceFinderDeepLevels = 7;

    /**
     *
     * @param array $namespacesConnections ['namespace' => 'connection_name'....]
     */
    public function __construct(array $namespacesConnections = [], int $namespaceFinderDeepLevels = 7)
    {
        $this->namespaceFinderDeepLevels = $namespaceFinderDeepLevels;
        foreach ($namespacesConnections as $k => $v) {
            $this->namespacesConnections[\trim($k, '\\')] = $v;
        }
        $this->sort();
    }

    /**
     * @inheritdoc
     *
     * @param string $namespace
     * @param string $connectionName
     *
     * @return void
     */
    public function addNamespaceConnection(string $namespace, string $connectionName): void
    {
        $this->namespacesConnections[trim($namespace, '\\')] = $connectionName;
        $this->sort();
    }

    protected function sort(): void
    {
        uksort(
            $this->namespacesConnections,
            fn($a, $b) => substr_count($b, '\\') <=> substr_count($a, '\\')
        );
    }

    /**
     * @inheritdoc
     *
     * @param string $namespace
     *
     * @return string|null
     */
    public function getNamespaceConnection(string $namespace): ?string
    {
        $namespace = trim($namespace, '\\');
        foreach ($this->namespacesConnections as $k => $v) {
            if (str_starts_with($namespace, $k)) {
                return $v;
            }
        }
        return null;
    }

    /**
     * Ищет название подключения по неймспейсу через трейс вызовов
     *
     * @return string|null
     *
     * @uses \Symbiotic\Database\NamespaceConnectionsConfigInterface::getNamespaceConnection()
     */
    public function findNamespaceConnectionName(): ?string
    {
        foreach (array_slice(debug_backtrace(1, $this->namespaceFinderDeepLevels+2), 2) as $v) {
            $class = isset($v['object']) ? get_class($v['object']) : ($v['class'] ?? null);
            if ($class && ($name = $this->getNamespaceConnection($class))) {
                return $name;
            }
        }

        return null;
    }
}