<?php

declare(strict_types=1);

namespace Symbiotic\Database;

use Symbiotic\Container\DIContainerInterface;
use Symbiotic\Core\AbstractBootstrap;
use Symbiotic\Core\CoreInterface;

use function _S\settings;

class CoreBootstrap extends AbstractBootstrap
{
    public function bootstrap(DIContainerInterface $core): void
    {
        /**
         * Кешируем конфиг подключений
         */
        $this->cached(
            $core,
            ConnectionsConfigInterface::class,
            static function (CoreInterface $container) {
                // Ставим подключения из настроек
                if (\is_array($items = settings($container, 'databases')?->all())) {
                    $default = settings($container, 'core')->get('default_database_connection');
                } // Ставим подключения из корневого конфига
                elseif (\is_array($dbConfig = $container->get('config')->get('database'))) {
                    $default = $dbConfig['default'] ?? null;
                    $items = $dbConfig['connections'];
                } else {
                    $default = null;
                    $items = [];
                }

                return new ConnectionsConfig($items, empty($default) ? 'default' : $default);
            }
        );

        /**
         * Конфиг названий подключений по неймспейсам
         *
         * @see     AppNamespaceConnectionProvider::register()
         * @used-by DatabaseManager::getNamespacesConfig()
         */
        $core->singleton(
            NamespaceConnectionsConfigInterface::class,
            static function () {
                return new NamespaceConnectionsConfig();
            }
        );

        /**
         * Менеджер подключений
         *
         * @uses \Symbiotic\Database\ConnectionsConfigInterface
         * @uses \Symbiotic\Database\NamespaceConnectionsConfigInterface
         */
        $core->singleton(
            DatabaseManager::class,
            static function (CoreInterface $app) {
                return new DatabaseManager(
                    $app->get(ConnectionsConfigInterface::class),
                    $app->get(NamespaceConnectionsConfigInterface::class)
                );
            }
        );
    }
}