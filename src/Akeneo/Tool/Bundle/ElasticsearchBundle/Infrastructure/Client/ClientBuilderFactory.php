<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\ElasticsearchBundle\Infrastructure\Client;

use Elasticsearch\Client as NativeClient;
use Elasticsearch\ClientBuilder;

final class ClientBuilderFactory
{
    public function __invoke(
        array $hosts,
        string|null $basicAuthUser,
        string|null $basicAuthPass,
        string|null $elasticsearchCloudId,
        string|null $elasticsearchApiKey,
        string|null $elasticsearchApiKeyId
    ): NativeClient
    {
        $clientBuilder = ClientBuilder::create();
        $clientBuilder->setHosts($hosts);

        if (!empty($basicAuthUser) && !empty($basicAuthPass)) {
            $clientBuilder->setBasicAuthentication($basicAuthUser, $basicAuthPass);
            return $clientBuilder->build();
        }

        if (!empty($elasticsearchCloudId) && !empty($elasticsearchApiKey) && !empty($elasticsearchApiKeyId)) {
            // This clear is important because the host is resolved from the cloud ID and per default the
            // hosts env variable contains localhost:9200. The client will have 2 hosts and tries to connect
            // in the wrong way.
            $clientBuilder->setHosts([]);
            $clientBuilder->setElasticCloudId($elasticsearchCloudId);
            $clientBuilder->setApiKey($elasticsearchApiKeyId, $elasticsearchApiKey);
            return $clientBuilder->build();
        }

        return $clientBuilder->build();
    }
}
