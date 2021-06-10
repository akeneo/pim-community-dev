<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\Bundle\MonitoringBundle\ServiceStatusChecker;

use Akeneo\Tool\Bundle\ElasticsearchBundle\ClientRegistry;

final class ElasticsearchChecker
{
    private ClientRegistry $clientRegistry;

    public function __construct(ClientRegistry $clientRegistry)
    {
        $this->clientRegistry = $clientRegistry;
    }

    public function status(): ServiceStatus
    {
        $failingIndexNames = [];

        try {
            foreach ($this->clientRegistry->getClients() as $client) {
                if (!$client->hasIndex()) {
                    $failingIndexNames[] = $client->getIndexName();
                }
            }

            return empty($failingIndexNames) ?
                ServiceStatus::ok() :
                ServiceStatus::notOk(sprintf('Elasticsearch failing indexes: %s', implode(',', $failingIndexNames)));
        } catch (\Exception $exception) {
            return ServiceStatus::notOk(sprintf('Elasticsearch exception: %s', $exception->getMessage()));
        }
    }
}
