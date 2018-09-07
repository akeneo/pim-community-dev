<?php

namespace Pim\Bundle\AnalyticsBundle\DataCollector;

use Akeneo\Tool\Component\Analytics\DataCollectorInterface;
use Elasticsearch\ClientBuilder;

/**
 * Collects Elasticsearch version.
 *
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SearchEngineDataCollector implements DataCollectorInterface
{
    /**
     * @var ClientBuilder
     */
    private $clientBuilder;

    /**
     * @var array
     */
    private $hosts;

    /**
     * @param ClientBuilder $clientBuilder
     * @param array $hosts
     */
    public function __construct(ClientBuilder $clientBuilder, array $hosts)
    {
        $this->clientBuilder = $clientBuilder;
        $this->hosts = $hosts;
    }

    /**
     * {@inheritdoc}
     */
    public function collect()
    {
        $client = $this->clientBuilder
            ->setHosts($this->hosts)
            ->build();

        $info = $client->info();

        $version = $info['version']['number'] ?? '';

        return ['elasticsearch_version' => $version];
    }
}
