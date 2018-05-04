<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\ElasticsearchBundle;

/**
 * Registry containing all dynamically instanciated Elasticsearch clients.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ClientRegistry
{
    /** @var array */
    private $esClients = [];

    /**
     * @param Client $client
     */
    public function register(Client $client): void
    {
        $this->esClients[] = $client;
    }

    /**
     * @return Client[]
     */
    public function getClients(): array
    {
        return $this->esClients;
    }
}
