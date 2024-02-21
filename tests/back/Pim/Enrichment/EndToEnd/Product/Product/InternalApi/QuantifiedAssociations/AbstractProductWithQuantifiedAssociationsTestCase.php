<?php

namespace AkeneoTest\Pim\Enrichment\EndToEnd\Product\Product\InternalApi\QuantifiedAssociations;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use AkeneoTest\Pim\Enrichment\EndToEnd\Product\EntityWithQuantifiedAssociations\AbstractQuantifiedAssociationsTestCase;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractProductWithQuantifiedAssociationsTestCase extends AbstractQuantifiedAssociationsTestCase
{
    protected function updateProductWithInternalApi(UuidInterface $productUuid, array $data): Response
    {
        $this->client->request(
            'POST',
            sprintf('/enrich/product/rest/%s', $productUuid->toString()),
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ],
            json_encode($data)
        );

        return $this->client->getResponse();
    }

    protected function getProductFromInternalApi(UuidInterface $productUuid): array
    {
        $this->client->request(
            'GET',
            sprintf('/enrich/product/rest/%s', $productUuid->toString()),
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ]
        );

        return json_decode($this->client->getResponse()->getContent(), true);
    }

    protected function updateNormalizedProduct(array $data, array $changes): array
    {
        unset($data['meta']);

        return array_merge_recursive($data, $changes);
    }

    protected function getProductUpdater(): ObjectUpdaterInterface
    {
        return self::getContainer()->get('pim_catalog.updater.product');
    }
}
