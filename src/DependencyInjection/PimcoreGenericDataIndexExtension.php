<?php
declare(strict_types=1);

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Commercial License (PCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PCL
 */

namespace Pimcore\Bundle\GenericDataIndexBundle\DependencyInjection;

use Exception;
use InvalidArgumentException;
use Pimcore\Bundle\GenericDataIndexBundle\Enum\SearchIndex\ClientType;
use Pimcore\Bundle\GenericDataIndexBundle\MessageHandler\DispatchQueueMessagesHandler;
use Pimcore\Bundle\GenericDataIndexBundle\Service\SearchIndex\SearchIndexConfigServiceInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */

/**
 * @internal
 */
class PimcoreGenericDataIndexExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     *
     * @throws Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../../config'));
        $loader->load('services.yaml');

        $this->registerIndexServiceParams($container, $config['index_service']);
    }

    /**
     * @throws Exception
     */
    public function prepend(ContainerBuilder $container): void
    {
        if ($container->hasExtension('doctrine_migrations')) {
            $loader = new YamlFileLoader(
                $container,
                new FileLocator(__DIR__ . '/../../config')
            );

            $loader->load('doctrine_migrations.yaml');
        }

        $filename = __DIR__ . '/../../config/doctrine.yaml';

        try {
            $config = Yaml::parseFile($filename);
        } catch (ParseException $e) {
            throw new InvalidArgumentException(sprintf('The file "%s" does not contain valid YAML.', $filename), 0, $e);
        }

        $container->prependExtensionConfig('doctrine', $config['doctrine']);
    }

    private function registerIndexServiceParams(ContainerBuilder $container, array $indexSettings): void
    {
        $definition = $container->getDefinition(SearchIndexConfigServiceInterface::class);
        $definition->setArgument('$clientType', $indexSettings['client_params']['client_type']);
        $definition->setArgument('$indexPrefix', $indexSettings['client_params']['index_prefix']);
        $definition->setArgument('$indexSettings', $indexSettings['index_settings']);
        $definition->setArgument('$searchSettings', $indexSettings['search_settings']);
        $definition->setArgument('$systemFieldsSettings', $indexSettings['system_fields_settings']);
        if ($indexSettings['client_params']['client_type'] === ClientType::OPEN_SEARCH->value) {
            $openSearchClientId = 'pimcore.open_search_client.' . $indexSettings['client_params']['client_name'];
            $container->setAlias('generic-data-index.opensearch-client', $openSearchClientId)
                ->setDeprecated(
                    'pimcore/generic-data-index-bundle',
                    '1.3',
                    'The "%alias_id%" service alias is deprecated and will be removed in version 2.0. ' .
                    'Please use "generic-data-index.search-client" instead.'
                );
        }
        $clientId = $this->getDefaultSearchClientId($indexSettings);
        $container->setAlias('generic-data-index.search-client', $clientId);

        $container->setParameter('generic-data-index.index-prefix', $indexSettings['client_params']['index_prefix']);

        $definition = $container->getDefinition(DispatchQueueMessagesHandler::class);
        $definition->setArgument('$queueSettings', $indexSettings['queue_settings']);
    }

    /**
     * @throws InvalidArgumentException
     */
    private function getDefaultSearchClientId(array $indexSettings): string
    {
        $clientType = $indexSettings['client_params']['client_type'];
        $clientName = $indexSettings['client_params']['client_name'];

        return match ($clientType) {
            ClientType::OPEN_SEARCH->value => 'pimcore.openSearch.custom_client.' . $clientName,
            ClientType::ELASTIC_SEARCH->value => 'pimcore.elasticsearch.custom_client.' . $clientName,
            default => throw new InvalidArgumentException(
                sprintf('Invalid client type: %s', $indexSettings['client_params']['client_type'])
            )
        };
    }
}
