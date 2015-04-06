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

class PmgElasticsearchExtensionTest extends \PMG\ElasticsearchBundle\TestCase
{
    private $builder;

    public static function classKeys()
    {
        return [
            ['connection_class'],
            ['connection_factory_class'],
            ['connection_pool_class'],
            ['serializer_class'],
        ];
    }

    /**
     * @dataProvider classKeys
     * @expectedException Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessageRegExp /Class .* does not exist/
     */
    public function testNonExistentClassesCauseErrorsInConfigurationWhenUsedAtRoot($key)
    {
        $this->loadConfigAndCompile([
            $key    => __NAMESPACE__.'\\ThisClassDoesNotExistAtAll',
        ]);
    }

    /**
     * @dataProvider classKeys
     * @expectedException Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessageRegExp /Class .* does not exist/
     */
    public function testNonExistentClassesCauseErrorsInConfigurationWhenUsedInSingleClient($key)
    {
        $this->loadConfigAndCompile([
            'default_client'    => 'example',
            'clients'           => [
                'example' => [
                    $key    => __NAMESPACE__.'\\ThisClassDoesNotExistAtAll',
                ],
            ],
        ]);
    }

    /**
     * @dataProvider classKeys
     * @expectedException Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage does not implement or subclass
     */
    public function testInvalidClassArgumentsCauseErrorsWhenUsedAtRoot($key)
    {
        $this->loadConfigAndCompile([
            $key    => \ArrayObject::class,
        ]);
    }

    /**
     * @dataProvider classKeys
     * @expectedException Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage does not implement or subclass
     */
    public function testInvalidClassArgumentsCauseErrorsWhenUsedInSingleClient($key)
    {
        $this->loadConfigAndCompile([
            'default_client'    => 'example',
            'clients'           => [
                'example' => [
                    $key    => \ArrayObject::class,
                ],
            ],
        ]);
    }

    public function testDefaultArgumentsBuildsAValidElasticsearchConnection()
    {
        $this->loadConfigAndCompile();

        $client = $this->container->get('pmg_elasticsearch.client');
        $this->assertInstanceOf(\Elasticsearch\Client::class, $client);

        $client2 = $this->container->get('pmg_elasticsearch.default.client');
        $this->assertSame($client, $client2);
    }

    protected function setUp()
    {
        $this->container = new ContainerBuilder();
        $this->container->registerExtension(new PmgElasticsearchExtension());
    }

    private function loadConfigAndCompile(array $config=[])
    {
        $this->container->loadFromExtension('pmg_elasticsearch', $config);
        $this->container->compile();
    }
}
