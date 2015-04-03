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
                    if (!$cls) {
                        return false;
                    }
                    $impl = class_implements($cls);
                    return !isset($impl[$interface]);
                })
                ->thenInvalid('%s does not implement'.$interface)
            ->end();
    }
}
