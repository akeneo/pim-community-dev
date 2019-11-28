<?php

namespace Specification\Akeneo\Platform\Bundle\MonitoringBundle\ServiceStatusChecker;

use Akeneo\Platform\Bundle\MonitoringBundle\ServiceStatusChecker\ElasticsearchChecker;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Bundle\ElasticsearchBundle\ClientRegistry;
use Doctrine\DBAL\Connection;
use PhpSpec\ObjectBehavior;

class ElasticsearchCheckerSpec extends ObjectBehavior
{
    function it_returns_a_ok_status_with_all_working_es_clients(
        ClientRegistry $clientRegistry,
        Client $productClient,
        Client $draftClient
    ) {
        $productClient->hasIndex()->willReturn(true);
        $draftClient->hasIndex()->willReturn(true);

        $clientRegistry->getClients()->willReturn([$productClient, $draftClient]);

        $this->beConstructedWith($clientRegistry);
        $this->shouldHaveType(ElasticsearchChecker::class);

        $status = $this->status();
        $status->isOk()->shouldReturn(true);
        $status->getMessage()->shouldReturn("OK");
    }

    function it_returns_a_ko_status_with_a_non_working_es_client(
        ClientRegistry $clientRegistry,
        Client $productClient,
        Client $draftClient
    ) {
        $productClient->hasIndex()->willReturn(true);
        $draftClient->hasIndex()->willReturn(false);
        $draftClient->getIndexName()->willReturn('pimee_index_draft');

        $clientRegistry->getClients()->willReturn([$productClient, $draftClient]);

        $this->beConstructedWith($clientRegistry);
        $this->shouldHaveType(ElasticsearchChecker::class);

        $status = $this->status();
        $status->isOk()->shouldReturn(false);
        $status->getMessage()->shouldReturn("Elasticsearch failing indexes: pimee_index_draft");
    }

    function it_returns_a_ko_status_with_all_es_clients_non_working(
        ClientRegistry $clientRegistry,
        Client $productClient,
        Client $draftClient
    ) {
        $productClient->hasIndex()->willReturn(false);
        $productClient->getIndexName()->willReturn('pimee_index_product');
        $draftClient->hasIndex()->willReturn(false);
        $draftClient->getIndexName()->willReturn('pimee_index_draft');

        $clientRegistry->getClients()->willReturn([$productClient, $draftClient]);

        $this->beConstructedWith($clientRegistry);
        $this->shouldHaveType(ElasticsearchChecker::class);

        $status = $this->status();
        $status->isOk()->shouldReturn(false);
        $status->getMessage()->shouldReturn("Elasticsearch failing indexes: pimee_index_product,pimee_index_draft");
    }
}
