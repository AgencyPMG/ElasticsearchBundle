<?php
/**
 * This file is part of PMG Elasticsearch Bundle
 *
 * @package     PMG\ElasticsearchBundle
 * @license     http://opensource.org/licenses/mit mit
 * @copyright   PMG <https://www.pmg.co>
 */

namespace PMG\ElasticsearchBundle;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * This just uses a real HttpKernel to test the bundle integration with its
 * default configuration. Basically we just make a request to elasticsearch
 * to make sure our elasticsearch stuff actually works.
 *
 * @group   acceptance
 */
class BundleTest extends WebTestCase
{
    public function testDefaultElasticsearchConfigurationWorksAndCanMakeRequests()
    {
        $client = $this->createClient();
        $client->request('GET', '/');

        $resp = $client->getResponse();

        $this->assertInstanceOf(JsonResponse::class, $resp);
        $body = json_decode($resp->getContent(), true);

        $this->assertArrayHasKey('tagline', $body);
        $this->assertEquals('You Know, for Search', $body['tagline']);
    }

    protected static function getKernelClass()
    {
        return TestKernel::class;
    }
}
