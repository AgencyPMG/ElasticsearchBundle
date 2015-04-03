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
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

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
    ];

    /**
     * {@inheritdoc}
     */
    public function loadInternal(array $config, ContainerBuilder $container)
    {
        $arguments = [];
        foreach ($this->optionsMap as $bundle => $es) {
            if (!empty($config[$bundle])) {
                $arguments[$es] = $config[$bundle];
            }
        }

        $container->setDefinition(
            'pmg_elasticsearch.client',
            new Definition(\Elasticsearch\Client::class, [
                $arguments,
            ])
        );
    }
}
