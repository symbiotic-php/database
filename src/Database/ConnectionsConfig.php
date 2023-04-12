<?php

declare(strict_types=1);

namespace Symbiotic\Database;

class ConnectionsConfig implements ConnectionsConfigInterface
{

    /**
     * @see \Symbiotic\Apps\Settings\Http\Controllers\Backend\Databases::add()
     */
    public function __construct(
        protected array $connections,
        protected string $defaultConnectionName = 'default'
    ) {}

    /**
     * @inheritdoc
     *
     * @return string
     */
    public function getDefaultConnectionName(): string
    {
        return $this->defaultConnectionName;
    }

    /**
     * @inheritdoc
     *
     * @return array
     */
    public function getConnections(): array
    {
        return $this->connections;
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
        return isset($this->connections[$name]);
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
        return $this->connections[$name] ?? null;
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
        $this->connections[$name] = $config;
    }

    /**
     * Установка подключения по умолчанию
     *
     * @param string $name
     *
     * @return void
     */
    public function setDefault(string $name): void
    {
        $this->defaultConnectionName = $name;
    }

    /**
     * Удаляет подключение
     *
     * @param string $name
     *
     * @return void
     */
    public function removeConnection(string $name): void
    {
        unset($this->connections[$name]);
    }

    public function __serialize(): array
    {
        return [
            'connections' => $this->connections,
            'defaultConnectionName' => $this->defaultConnectionName
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->connections = $data['connections'];
        $this->defaultConnectionName = $data['defaultConnectionName'];
    }
}