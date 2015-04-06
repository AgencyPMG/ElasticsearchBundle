<?php
/**
 * This file is part of PMG Elasticsearch Bundle
 *
 * @package     PMG\ElasticsearchBundle
 * @license     http://opensource.org/licenses/mit mit
 * @copyright   PMG <https://www.pmg.co>
 */

namespace PMG\ElasticsearchBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Elasticsearch\Connections\ConnectionInterface;
use Elasticsearch\Connections\ConnectionFactory;
use Elasticsearch\ConnectionPool\AbstractConnectionPool;
use Elasticsearch\ConnectionPool\Selectors\SelectorInterface;
use Elasticsearch\Serializers\SerializerInterface;

/**
 * PmgElasticsearchBundle Configuration Structure.
 *
 * @since   0.1
 */
final class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $tree = new TreeBuilder();
        $root = $tree->root('pmg_elasticsearch');
        $root
            ->beforeNormalization()
                ->ifTrue(function ($config) {
                    // if we have only a single connection, we need to convert it
                    // to our "multiple clients" format.
                    return is_array($config) && !array_key_exists('clients', $config);
                })
                ->then(function (array $config) {
                    return [
                        'default_client'    => 'default',
                        'clients'           => ['default' => $config],
                    ];
                })
            ->end();

        $children = $root->children()
            ->append($this->createClientNode())
            ->scalarNode('default_client');

        return $tree;
    }

    private function createClientNode()
    {
        $tree = new TreeBuilder();
        $node = $tree->root('clients');

        $clients = $node
            ->requiresAtLeastOneElement()
            ->useAttributeAsKey('name')
            ->prototype('array')
            ->children();

        $this->addClassNode($clients, 'connection_class', ConnectionInterface::class);
        $this->addClassNode($clients, 'connection_factory_class', ConnectionFactory::class);
        $this->addClassNode($clients, 'connection_pool_class', AbstractConnectionPool::class);
        $this->addClassNode($clients, 'selector_class', SelectorInterface::class);
        $this->addClassNode($clients, 'serializer_class', SerializerInterface::class);

        $clients
            ->booleanNode('sniff_on_start')->end()
            ->booleanNode('enable_logging')->defaultTrue()->end()
            ->arrayNode('hosts')
                ->defaultValue(['http://localhost:9200'])
                ->requiresAtLeastOneElement()
                ->prototype('scalar')
            ->end();

        return $node;
    }

    private function addClassNode(NodeBuilder $node, $name, $interface)
    {
        $node->scalarNode($name)
            ->validate()
                ->ifTrue(function ($cls) {
                    return $cls && !class_exists($cls);
                })
                ->thenInvalid('Class %s does not exist')
            ->end()
            ->validate()
                ->ifTrue(function ($cls) use ($interface) {
                    return $cls && !is_subclass_of($cls, $interface);
                })
                ->thenInvalid('%s does not implement or subclass '.$interface)
            ->end();
    }
}
