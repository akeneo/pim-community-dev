<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\ErrorManagement\Persistence;

use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\Read\BusinessError;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Persistence\Query\SelectLastConnectionBusinessErrorsQueryInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ElasticsearchSelectLastConnectionBusinessErrorsQuery implements SelectLastConnectionBusinessErrorsQueryInterface
{
    public function __construct(private Client $esClient)
    {
    }

    public function execute(string $connectionCode, ?string $endDate = null, int $limit = 100): array
    {
        [$from, $to] = $this->getDateTimeInterval($endDate);

        $result = $this->esClient->search([
            'query' => [
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'filter' => [
                                [
                                    'term' => [
                                        'connection_code' => $connectionCode,
                                    ],
                                ],
                                [
                                    'range' => [
                                        'error_datetime' => [
                                            'gte' => $from->format(\DateTimeInterface::ATOM),
                                            'lte' => $to->format(\DateTimeInterface::ATOM),
                                        ],
                                    ]
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'sort' => [
                'error_datetime' => 'desc',
            ],
            'size' => $limit,
        ]);

        $businessErrors = [];

        foreach ($result['hits']['hits'] as $row) {
            $data = $row['_source'];

            $businessErrors[] = new BusinessError(
                $data['connection_code'],
                \DateTimeImmutable::createFromFormat(\DateTimeInterface::ATOM, $data['error_datetime'], new \DateTimeZone('UTC')),
                \json_encode($data['content'], JSON_THROW_ON_ERROR)
            );
        }

        return $businessErrors;
    }

    /**
     * @return array{\DateTimeImmutable, \DateTimeImmutable}
     */
    private function getDateTimeInterval(?string $endDate = null): array
    {
        $utc = new \DateTimeZone('UTC');
        $to = new \DateTimeImmutable('now', $utc);
        if (null !== $endDate) {
            $to = \DateTimeImmutable::createFromFormat('Y-m-d', $endDate, $utc);
            if (false === $to) {
                $to = new \DateTimeImmutable('now', $utc);
            }
        }

        $to = $to->setTime(0, 0)->add(new \DateInterval('P1D'));
        $from = $to->sub(new \DateInterval('P7D'));

        return [$from, $to];
    }
}
