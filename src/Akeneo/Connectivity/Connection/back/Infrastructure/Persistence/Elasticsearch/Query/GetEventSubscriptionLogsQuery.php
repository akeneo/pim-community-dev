<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Persistence\Elasticsearch\Query;

use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Query\GetEventSubscriptionLogsQueryInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;

/**
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetEventSubscriptionLogsQuery implements GetEventSubscriptionLogsQueryInterface
{
    const MAX_NUMBER_OF_NOTICE_AND_INFO_LOGS = 100;
    const MAX_LIFETIME_OF_WARNING_AND_ERROR_LOGS = 4320; // 72h

    private Client $elasticsearchClient;

    public function __construct(Client $elasticsearchClient)
    {
        $this->elasticsearchClient = $elasticsearchClient;
    }

    public function execute(string $connectionCode): \Traversable
    {
        return $this->elasticsearchClient->scroll(
            [
                'sort' => [
                    'timestamp' => [
                        'order' => 'ASC',
                    ],
                ],
                'query' => [
                    'match_all' => new \stdClass(),
                    /*
                    'constant_score' => [
                        'filter' => [
                            'bool' => [
                                'filter' => ['term' => ['connection_code' => $connectionCode]],
                            ],
                        ],
                    ],*/
                ],
            ],
            1000
        );
    }
}
