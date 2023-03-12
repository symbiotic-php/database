<?php

declare(strict_types=1);

namespace Symbiotic\Database;

use Symbiotic\Apps\ApplicationInterface;
use Symbiotic\Core\CoreInterface;
use Symbiotic\Core\ServiceProvider;
use Symbiotic\Packages\PackageConfig;
use Symbiotic\Packages\PackagesRepositoryInterface;
use Symbiotic\Settings\SettingsInterface;

/**
 * @property ApplicationInterface $app
 */
class AppNamespaceConnectionProvider extends ServiceProvider
{
    public function register(): void
    {
        /**
         * @var  DatabaseManager $manager
         * @var  CoreInterface   $core
         */
        $core = $this->app->get(CoreInterface::class);
        $manager = $core->get(DatabaseManager::class);
        $appConnectionName = $this->app->get(SettingsInterface::class)->get('database_connection_name');

        if (is_string($appConnectionName)) {
            if (!$manager->hasConnection($appConnectionName)) {
                throw new DatabaseException(
                    'Connection with name [' . $appConnectionName . '] not found for Application '
                    . $this->app->getId() . '!',
                    4046
                );
            }
            $namespaceResolver = $manager->getNamespacesConfig();
            /**
             * @var PackageConfig $packageConfig
             */
            $packageConfig = $core->get(PackagesRepositoryInterface::class)->getPackageConfig($this->app->getId());
            foreach (array_keys($packageConfig->get('namespaces') ?? []) as $v) {
                $namespaceResolver->addNamespaceConnection($v, $appConnectionName);
            }
        }
    }
}