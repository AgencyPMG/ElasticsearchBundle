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
use Elasticsearch\ClientBuilder;

/**
 * Our DI container extension.
 *
 * @since   0.1
 */
final class PmgElasticsearchExtension extends ConfigurableExtension
{
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
            "pmg_elasticsearch.{$name}.client_builder",
            new Definition(ClientBuilder::class)
        );
        $factory->addMethodCall('setSniffOnStart', [!empty($config['sniff_on_start'])]);
        $factory->addMethodCall('setHosts', [$config['hosts']]);

        if ($config['enable_logging']) {
            $factory->addTag('monolog.logger', [
                'channel'   => 'pmg_elasticsearch'
            ]);
            $factory->addMethodCall('setLogger', [
                new Reference('logger', ContainerInterface::NULL_ON_INVALID_REFERENCE),
            ]);
            $factory->addMethodCall('setTracer', [
                new Reference('logger', ContainerInterface::NULL_ON_INVALID_REFERENCE),
            ]);
        }

        if (!empty($config['connection_factory'])) {
            $factory->addMethodCall('setConnectionFactory', [new Reference($config['connection_factory'])]);
        }

        if (!empty($config['connection_pool'])) {
            $factory->addMethodCall('setConnectionPool', [new Reference($config['connection_pool'])]);
        }

        if (!empty($config['serializer'])) {
            $factory->addMethodCall('setSerializer', [new Reference($config['serializer'])]);
        } elseif (!empty($config['serializer_class'])) {
            $factory->addMethodCall('setSerializer', [$config['serializer_class']]);
        }

        $client = $container->setDefinition(
            "pmg_elasticsearch.{$name}.client",
            new Definition(\Elasticsearch\Client::class)
        );
        $client->setFactory([new Reference("pmg_elasticsearch.{$name}.client_builder"), 'build']);
    }
}
