<?php
/**
 * This file is part of PMG Elasticsearch Bundle
 *
 * @package     PMG\ElasticsearchBundle
 * @license     http://opensource.org/licenses/mit mit
 * @copyright   PMG <https://www.pmg.co>
 */

namespace PMG\ElasticsearchBundle;

use Psr\Log\NullLogger;

class ElasticsearchFactoryTest extends TestCase
{
    /**
     * @group regression
     * @group https://github.com/AgencyPMG/ElasticsearchBundle/issues/1
     */
    public function testFactoryCreatesClientsWithPassedInLogObjects()
    {
        $logger = new NullLogger();
        // this is a bit of a hack to get around the fact we can't verify
        // the logger is passed correctly after the client is created. Instead
        // fake a abstract class mock and swap out the `newClient` method.
        $factory = $this->getMockBuilder(ElasticsearchFactory::class)
            ->setConstructorArgs([
                [
                    'hosts'     => [
                        'http://localhost:9200',
                    ],
                    'logging'   => true,
                ],
                $logger
            ])
            ->setMethods(['newClient'])
            ->getMockForAbstractClass();

        $factory->expects($this->once())
            ->method('newClient')
            ->with($this->callback(function (array $params) use ($logger) {
                $this->assertSame($params['logObject'], $logger);
                $this->assertSame($params['traceObject'], $logger);
                return true;
            }));


        $factory->create();
    }
}
