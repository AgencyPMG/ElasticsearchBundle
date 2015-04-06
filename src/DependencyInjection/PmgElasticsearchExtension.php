<?php
/**
 * This file is part of PMG Elasticsearch Bundle
 *
 * @package     PMG\ElasticsearchBundle
 * @license     http://opensource.org/licenses/mit mit
 * @copyright   PMG <https://www.pmg.co>
 */

namespace PMG\ElasticsearchBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;
use PMG\ElasticsearchBundle\ElasticsearchFactory;

/**
 * Our DI container extension.
 *
 * @since   0.1
 */
final class PmgElasticsearchExtension extends ConfigurableExtension
{
    private $optionsMap = [
        'connection_class'              => 'connectionClass',
        'connection_factory_class'      => 'connectionFactoryClass',
        'connectoin_pool_class'         => 'connectionPoolClass',
        'selector_class'                => 'selectorClass',
        'serializer_class'              => 'serializerClass',
        'sniff_on_start'                => 'sniffOnStart',
        'enable_logging'                => 'logging',
    ];

    /**
     * {@inheritdoc}
     */
    public function loadInternal(array $config, ContainerBuilder $container)
    {
        foreach ($config['clients'] as $clientName => $clientConfig) {
            $this->addClient($container, $clientName, $clientConfig);
        }

        $container->setAlias('pmg_elasticsearch.client', sprintf(
            'pmg_elasticsearch.%s.client',
            $config['default_client']
        ));
    }

    private function addClient(ContainerBuilder $container, $name, array $config)
    {
        $factory = $container->setDefinition(
            "pmg_elasticsearch.{$name}.client_factory",
            new Definition(ElasticsearchFactory::class, [
                $this->createClientArguments($config),
                new Reference('logger', ContainerInterface::NULL_ON_INVALID_REFERENCE),
            ])
        );
        $factory->addTag('monolog.logger', [
            'channel'   => 'pmg_elasticsearch'
        ]);

        $client = $container->setDefinition(
            "pmg_elasticsearch.{$name}.client",
            new Definition(\Elasticsearch\Client::class)
        );
        $client->setFactory([new Reference("pmg_elasticsearch.{$name}.client_factory"), 'create']);
    }

    private function createClientArguments(array $config)
    {
        $arguments = [];
        foreach ($this->optionsMap as $bundle => $es) {
            if (!empty($config[$bundle])) {
                $arguments[$es] = $config[$bundle];
            }
        }

        return $arguments;
    }
}
