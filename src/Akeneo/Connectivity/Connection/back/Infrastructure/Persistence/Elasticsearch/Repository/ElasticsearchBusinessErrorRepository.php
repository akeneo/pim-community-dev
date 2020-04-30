<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Persistence\Elasticsearch\Repository;

use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\Write\BusinessError;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Persistence\Repository\BusinessErrorRepository;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ElasticsearchBusinessErrorRepository implements BusinessErrorRepository
{
    /** @var Client */
    private $errorClient;

    public function __construct(Client $errorClient)
    {
        $this->errorClient = $errorClient;
    }

    public function bulkInsert(array $businessErrors): void
    {
        if (0 === count($businessErrors)) {
            return;
        }

        $dateTime = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));

        $documents = array_map(function (BusinessError $businessError) use ($dateTime) {
            return $businessError->normalize($dateTime);
        }, $businessErrors);

        $this->errorClient->bulkIndexes($documents);
    }
}
