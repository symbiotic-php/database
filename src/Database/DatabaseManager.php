<?php

declare(strict_types=1);

namespace Symbiotic\Database;


class DatabaseManager implements ConnectionsConfigInterface,
                                 NamespaceConnectionsConfigInterface,
                                 \Stringable,
                                 \ArrayAccess
{


    /**
     * Активность определения подключений через неймспейсы
     *
     * @used-by DatabaseManager::getNamespaceConnection()
     * @used-by DatabaseManager::__toString()
     *
     * @param bool $flag
     *
     * @return void
     */
    protected bool $enableNamespacesConnections = true;

    /**
     * @param ConnectionsConfigInterface               $config
     * @param NamespaceConnectionsConfigInterface|null $namespacesConfig
     */
    public function __construct(
        protected ConnectionsConfigInterface $config,
        protected ?NamespaceConnectionsConfigInterface $namespacesConfig = null
    ) {}


    /**
     * @param array $config = [
     *                      'default' => 'default_connection_name',
     *                      'connections' => ['name' => [config],/...],
     *                      'namespaces' => ['\\NamespaceMy\\Models' => 'connection_name', ....]
     *                      ]
     *
     * @return static
     */
    public static function fromArray(array $config): static
    {
        return new static(
            new ConnectionsConfig($config['connections'], $config['default'] ?? 'default'),
            new NamespaceConnectionsConfig($config['namespaces'] ?? [])
        );
    }

    /**
     * Установка конфигурации соединений
     *
     * @param ConnectionsConfigInterface $config
     *
     * @return void
     */
    public function setConfig(ConnectionsConfigInterface $config): void
    {
        $this->config = $config;
    }

    /**
     * Установка нового Детектора подключений по неймспейсам
     *
     * @param NamespaceConnectionsConfigInterface $resolver
     *
     * @return void
     */
    public function setNamespacesConfig(NamespaceConnectionsConfigInterface $resolver): void
    {
        $this->namespacesConfig = $resolver;
    }

    /**
     * @inheritdoc
     *
     * @return array|\ArrayAccess
     */
    public function getConnections(): array|\ArrayAccess
    {
        return $this->config->getConnections();
    }

    /**
     * @inheritdoc
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasConnection(string $name): bool
    {
        return $this->config->hasConnection($name);
    }

    /**
     * @inheritdoc
     *
     * @param string $name
     *
     * @return array|null
     */
    public function getConnection(string $name): ?array
    {
        // Если передали нейм
        if (!empty($namespaceName = $this->getNamespaceConnection($name))) {
            $name = $namespaceName;
        }
        return $this->config->getConnection($name);
    }

    /**
     * @inheritdoc
     *
     * @param string $name
     *
     * @return void
     */
    public function removeConnection(string $name): void
    {
        $this->config->removeConnection($name);
    }

    /**
     * Возвращает детектор подключений по неймспейсам
     *
     * @return NamespaceConnectionsConfigInterface|null
     */
    public function getNamespacesConfig(): ?NamespaceConnectionsConfigInterface
    {
        return $this->namespacesConfig;
    }

    /**
     * @inheritdoc
     *
     * @return string
     */
    public function getDefaultConnectionName(): string
    {
        return $this->config->getDefaultConnectionName();
    }

    /**
     * @inheritdoc
     *
     * @param array  $config
     * @param string $name
     *
     * @return void
     */
    public function addConnection(array $config, string $name = 'default'): void
    {
        $this->config->addConnection($config, $name);
    }

    /**
     * @inheritdoc
     *
     * @param string $name
     *
     * @return void
     */
    public function setDefault(string $name): void
    {
        $this->config->setDefault($name);
    }

    /**
     * @param string $namespace      Базовый префикс
     * @param string $connectionName Название соединения
     *
     * @return void
     */
    public function addNamespaceConnection(string $namespace, string $connectionName): void
    {
        $this->namespacesConfig->addNamespaceConnection($namespace, $connectionName);
    }

    /**
     * Включает или выключает установку подключений через неймспейсы
     *
     * @used-by DatabaseManager::getNamespaceConnection()
     * @used-by DatabaseManager::__toString()
     *
     * @param bool $flag
     *
     * @return void
     */
    public function activateNamespaceFinder(bool $flag): void
    {
        $this->enableNamespacesConnections = $flag;
    }

    /**
     * Включен ли поиск подключений по неймспейсам
     *
     * @return bool
     *
     * @see NamespaceConnectionsConfigInterface
     */
    public function isActiveNamespaceFinder(): bool
    {
        return $this->enableNamespacesConnections;
    }

    /**
     *  Вернет название подключения для неймспейса
     *
     * @info  Строка если найдено или Null
     * @info  Также вернет null если определение по неймспецсам отключено!
     *
     * @param string $namespace
     *
     * @return string|null
     */
    public function getNamespaceConnection(string $namespace): ?string
    {
        return $this->enableNamespacesConnections ? $this->namespacesConfig->getNamespaceConnection($namespace) : null;
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
        return $this->enableNamespacesConnections ? $this->namespacesConfig->findNamespaceConnectionName() : null;
    }

    /**
     * При приведении к строке возвращаем подключение по умолчанию или подключение для неймспейса
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->findNamespaceConnectionName() ?? $this->getDefaultConnectionName();
    }

    /************
     * ArrayAccess
     ************/
    /**
     * @param mixed $offset
     *
     * @return bool
     */
    public function offsetExists(mixed $offset): bool
    {
        return $this->config->hasConnection($offset);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->config->getConnection($offset);
    }

    /**
     * @param string $offset
     * @param array  $value
     *
     * @return void
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->config->addConnection($value, $offset);
    }

    /**
     * @param mixed $offset
     *
     * @return void
     */
    public function offsetUnset(mixed $offset): void
    {
        $this->config->removeConnection($offset);
    }
}