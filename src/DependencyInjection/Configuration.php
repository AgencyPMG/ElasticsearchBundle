<?php
/**
 * This file is part of PMG Elasticsearch Bundle
 *
 * @package     PMG\ElasticsearchBundle
 * @license     http://opensource.org/licenses/mit mit
 * @copyright   PMG <https://www.pmg.co>
 */

namespace PMG\ElasticsearchBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
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

        $children = $root->children();

        $this->addClassNode($children, 'connection_class', ConnectionInterface::class);
        $this->addClassNode($children, 'connection_factory_class', ConnectionFactory::class);
        $this->addClassNode($children, 'connection_pool_class', AbstractConnectionPool::class);
        $this->addClassNode($children, 'selector_class', SelectorInterface::class);
        $this->addClassNode($children, 'serializer_class', SerializerInterface::class);

        return $tree;
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
