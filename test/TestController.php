<?php
/**
 * This file is part of PMG Elasticsearch Bundle
 *
 * @package     PMG\ElasticsearchBundle
 * @license     http://opensource.org/licenses/mit mit
 * @copyright   PMG <https://www.pmg.co>
 */

namespace PMG\ElasticsearchBundle;

use Symfony\Bundle\FrameworkBundle\Controller\Controller as SymfonyController;
use Symfony\Component\HttpFoundation\JsonResponse;

final class TestController extends SymfonyController
{
    public function __invoke()
    {
        return new JsonResponse($this->get('pmg_elasticsearch.client')->info());
    }
}
