<?php

namespace Akeneo\Bundle\ElasticsearchBundle\Registry;

use Akeneo\Bundle\ElasticsearchBundle\Client;

/**
 * Registry of Elasticsearch clients.
 */
interface ClientRegistryInterface
{
    /**
     * @param Client $client
     *
     * @return mixed
     */
    public function register(Client $client): void;

    /**
     * @return Client[]
     */
    public function getClients(): array;
}
