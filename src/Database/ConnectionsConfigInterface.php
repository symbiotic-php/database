<?php

declare(strict_types=1);

namespace Symbiotic\Database;

interface ConnectionsConfigInterface
{

    /**
     * Возвращает массив настроек всех подключений
     *
     * @return iterable|\ArrayAccess [ 'name' => [config]....]
     */
    public function getConnections(): array|\ArrayAccess;

    /**
     * Проверяет есть ли настройки подключения по ключу
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasConnection(string $name): bool;

    /**
     * Возвращает название подключения по умолчанию
     *
     * @return string
     */
    public function getDefaultConnectionName(): string;

    /**
     * Возвращает настройки соединения по ключу
     *
     * @param string $name
     *
     * @return array|null
     */
    public function getConnection(string $name): ?array;

    /**
     * Добавление подключения к базе данных
     *
     * @param array  $config
     * @param string $name
     *
     * @return void
     */
    public function addConnection(array $config, string $name = 'default'): void;


    /**
     * Установка подключения по умолчанию
     *
     * @used-by ConnectionsConfigInterface::getDefaultConnectionName()
     * @param string $name
     *
     * @return void
     */
    public function setDefault(string $name): void;

    /**
     * Удаляет подключение
     *
     * @param string $name
     *
     * @return void
     */
    public function removeConnection(string $name): void;

}