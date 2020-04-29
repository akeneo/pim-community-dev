<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Persistence\Elasticsearch\Query;

use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\Read\BusinessError;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Persistence\Query\SelectLastConnectionBusinessErrorsQuery;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ElasticsearchSelectLastConnectionBusinessErrorsQuery implements SelectLastConnectionBusinessErrorsQuery
{
    /** @var Client */
    private $esClient;

    public function __construct(Client $esClient)
    {
        $this->esClient = $esClient;
    }

    public function execute(string $connectionCode, string $endDate = null, int $limit = 100): array
    {
        [$from, $to] = $this->getDateTimeInterval($endDate);

        $result = $this->esClient->search([
            'query' => [
                'bool' => [
                    'filter' => [
                        [
                            'term' => [
                                'connection_code' => $connectionCode,
                            ],
                        ],
//                        [
//                            'range' => [
//                                'date_time' => [
//                                    'gte' => $from->format('Y-m-d H:i:s'),
//                                    'lte' => $to->format('Y-m-d H:i:s'),
//                                ]
//                            ]
//                        ],
                    ],
                ],
            ],
            'size' => $limit,
        ]);

        dd($result);

        return array_map(function (array $row) {
            $data = $row['_source'];

            return new BusinessError(
                $data['connection_code'],
                \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $data['date_time'], new \DateTimeZone('UTC')),
                json_encode($data['content'])
            );
        }, $result['hits']['hits']);
    }

    /**
     * @return array{\DateTimeImmutable, \DateTimeImmutable}
     */
    private function getDateTimeInterval(string $endDate = null): array
    {
        $to = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        if (null !== $endDate) {
            $to = \DateTimeImmutable::createFromFormat('Y-m-d', $endDate, new \DateTimeZone('UTC'));
            if (false === $to) {
                $to = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
            }
        }

        $to = $to->setTime(0, 0)->add(new \DateInterval('P1D'));
        $from = $to->sub(new \DateInterval('P7D'));

        return [$from, $to];
    }
}
