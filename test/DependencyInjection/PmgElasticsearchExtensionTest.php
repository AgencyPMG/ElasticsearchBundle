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
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

class PmgElasticsearchExtensionTest extends \PMG\ElasticsearchBundle\TestCase
{
    private $builder;

    public static function classKeys()
    {
        return [
            ['serializer_class'],
        ];
    }

    /**
     * @dataProvider classKeys
     */
    public function testNonExistentClassesCauseErrorsInConfigurationWhenUsedAtRoot($key)
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessageRegExp('/Class .* does not exist/');

        $this->loadConfigAndCompile([
            $key    => __NAMESPACE__.'\\ThisClassDoesNotExistAtAll',
        ]);
    }

    /**
     * @dataProvider classKeys
     */
    public function testNonExistentClassesCauseErrorsInConfigurationWhenUsedInSingleClient($key)
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessageRegExp('/Class .* does not exist/');

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
     */
    public function testInvalidClassArgumentsCauseErrorsWhenUsedAtRoot($key)
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('does not implement or subclass');

        $this->loadConfigAndCompile([
            $key    => \ArrayObject::class,
        ]);
    }

    /**
     * @dataProvider classKeys
     */
    public function testInvalidClassArgumentsCauseErrorsWhenUsedInSingleClient($key)
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('does not implement or subclass');

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
