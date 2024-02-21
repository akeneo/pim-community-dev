<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\ErrorManagement\Persistence;

use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\Write\BusinessError;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Persistence\Repository\BusinessErrorRepositoryInterface;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ConnectionCode;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ElasticsearchBusinessErrorRepository implements BusinessErrorRepositoryInterface
{
    public function __construct(private Client $errorClient)
    {
    }

    public function bulkInsert(ConnectionCode $connectionCode, array $businessErrors): void
    {
        if (0 === \count($businessErrors)) {
            return;
        }

        $code = (string) $connectionCode;
        $documents = \array_map(fn (BusinessError $businessError): array => \array_merge(['connection_code' => $code], $businessError->normalize()), $businessErrors);

        $this->errorClient->bulkIndexes($documents);
    }
}
