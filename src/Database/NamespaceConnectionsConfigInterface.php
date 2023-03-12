<?php

declare(strict_types=1);

namespace Symbiotic\Database;

interface NamespaceConnectionsConfigInterface
{

    /**
     * @param string $namespace      Базовый префикс
     * @param string $connectionName Название соединения
     *
     * @return void
     */
    public function addNamespaceConnection(string $namespace, string $connectionName): void;


    /**
     *  Вернет название подключение
     *
     *  Строка если не найдено или Null
     *
     * @param string $namespace
     *
     * @return string|null
     */
    public function getNamespaceConnection(string $namespace): ?string;


    /**
     * Ищет название подключения по неймспейсу через трейс вызовов
     *
     * @return string|null
     *
     * @uses \Symbiotic\Database\NamespaceConnectionsConfigInterface::getNamespaceConnection()
     */
    public function findNamespaceConnectionName(): ?string;

}