<?php

declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Installer\Infrastructure\Query\Elasticsearch;

use Akeneo\Platform\Installer\Domain\Query\Elasticsearch\ResetIndexesInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\ClientRegistry;

final class ResetIndexes implements ResetIndexesInterface
{
    public function __construct(
        private ClientRegistry $clientRegistry
    ) {}
    public function reset(): void
    {
        $clients = $this->clientRegistry->getClients();

        foreach ($clients as $client) {
            $client->resetIndex();
        }
    }
}
