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
use Elasticsearch\Connections\ConnectionFactoryInterface;
use Elasticsearch\ConnectionPool\ConnectionPoolInterface;
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
        [$tree, $root] = $this->createTreeBuilder('pmg_elasticsearch');

        $root
            ->beforeNormalization()
            ->ifTrue(function ($config) {
                    // if we have only a single connection, we need to convert it
                    // to our "multiple clients" format.
                    return is_array($config) && !array_key_exists('clients', $config);
                })
                ->then(function (array $config) {
                    $fixed = [
                        'default_client' => $config['default_client'] ?? 'default',
                    ];
                    unset($config['default_client']);

                    $fixed['clients'] = [
                        $fixed['default_client'] => $config,
                    ];

                    return $fixed;
                })
            ->end();

        $children = $root->children()
            ->append($this->createClientNode())
            ->scalarNode('default_client');

        return $tree;
    }

    private function createClientNode()
    {
        [$tree, $node] = $this->createTreeBuilder('clients');

        $clients = $node
            ->requiresAtLeastOneElement()
            ->useAttributeAsKey('name')
            ->prototype('array')
            ->children();

        $this->addClassNode($clients, 'serializer_class', SerializerInterface::class)
            ->info(sprintf('Alternative to `serializer`. The class to use for the serializer, must implement %s', SerializerInterface::class))
        ->end();

        $clients
            ->scalarNode('connection_factory')
                ->info(sprintf('A service identifier that creates an instance of %s', ConnectionFactoryInterface::class))
            ->end()
            ->scalarNode('connection_pool')
                ->info(sprintf('A service identifier that creates an instance of %s', ConnectionPoolInterface::class))
            ->end()
            ->scalarNode('serializer')
                ->info(sprintf('A service identifier that creates an instance of %s', SerializerInterface::class))
            ->end()
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
        return $node->scalarNode($name)
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

    private function createTreeBuilder(string $rootName) : array
    {
        // compat: symfony < 4.1
        if (method_exists(TreeBuilder::class, 'getRootNode')) {
            $tree = new TreeBuilder($rootName);
            $root = $tree->getRootNode();
        } else {
            $tree = new TreeBuilder();
            $root = $tree->root($rootName);
        }

        return [$tree, $root];
    }
}
