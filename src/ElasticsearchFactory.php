<?php
/**
 * This file is part of PMG Elasticsearch Bundle
 *
 * @package     PMG\ElasticsearchBundle
 * @license     http://opensource.org/licenses/mit mit
 * @copyright   PMG <https://www.pmg.co>
 */

namespace PMG\ElasticsearchBundle;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Create new elasticsearch objects from container configuration and loggers.
 *
 * @since   0.1
 */
class ElasticsearchFactory
{
    private $params;

    private $logger;

    public function __construct(array $params, LoggerInterface $logger=null)
    {
        $this->params = $params;
        $this->logger = $logger ?: new NullLogger();
    }

    public function create()
    {
        $params = $this->params;
        if (!empty($params['logging'])) {
            $params['logObject'] = $this->logger;
            $params['traceObject'] = $this->logger;
        }

        return $this->newClient($params);
    }

    protected function newClient(array $params)
    {
        return new \Elasticsearch\Client($params);
    }
}
