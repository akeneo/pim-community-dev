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
    /** @var ClientRegistry */
    private $clientRegistry;

    public function __construct(ClientRegistry $clientRegistry)
    {
        $this->clientRegistry = $clientRegistry;
    }

    public function status(): ServiceStatus
    {
        $statusOk = true;
        $failingIndexNames = [];

        foreach ($this->clientRegistry->getClients() as $client) {
            if (!$client->hasIndex()) {
                $statusOk = false;
                $failingIndexNames[] = $client->getIndexName();
            }
        }

        if ($statusOk) {
            $message = 'OK';
        } else {
            $message = 'Elasticsearch failing indexes: '.implode(',', $failingIndexNames);
        }

        return new ServiceStatus($statusOk, $message);
    }
}
