<?php

namespace Specification\Akeneo\Platform\Bundle\MonitoringBundle\ServiceStatusChecker;

use Akeneo\Platform\Bundle\MonitoringBundle\ServiceStatusChecker\FileStorageChecker;
use League\Flysystem\MountManager;
use PhpSpec\ObjectBehavior;

class FileStorageCheckerSpec extends ObjectBehavior
{
    function let(
        MountManager $mountManager,
        FilesystemInterface $assetFilesystem,
        FilesystemInterface $catalogFilesystem,
        FilesystemInterface $tmpFilesystem
    ) {
        $filesystemConfig = [
            'asset_storage' => ['adapter' => 'asset_storage_adapter', 'mount' => 'assetStorage' ],
            'catalog_storage' => ['adapter' => 'catalog_storage_adapter', 'mount' => 'catalogStorage' ],
            'tmp_storage' => ['adapter' => 'tmp_storage_adapter', 'mount' => 'tmpStorage' ],
        ];
        $mountManager->getFilesystem('assetStorage')->willReturn($assetFilesystem);
        $mountManager->getFilesystem('catalogStorage')->willReturn($catalogFilesystem);
        $mountManager->getFilesystem('tmpStorage')->willReturn($tmpFilesystem);

        $this->beConstructedWith($mountManager, $filesystemConfig);
    }

    function it_returns_a_ok_status_with_all_working_filesystems() {
        $this->shouldHaveType(FileStorageChecker::class);

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
