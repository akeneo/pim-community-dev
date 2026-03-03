<?php

namespace AkeneoTest\Pim\Enrichment\EndToEnd\Product\ProductModel\InternalApi\QuantifiedAssociations;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Storage\Indexer\ProductModelIndexerInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use AkeneoTest\Pim\Enrichment\EndToEnd\Product\EntityWithQuantifiedAssociations\AbstractQuantifiedAssociationsTestCase;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractProductModelWithQuantifiedAssociationsTestCase extends AbstractQuantifiedAssociationsTestCase
{
    protected function updateProductModelWithInternalApi(string $productModelId, array $data): Response
    {
        $this->client->request(
            'POST',
            sprintf('/enrich/product-model/rest/%s', $productModelId),
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ],
            json_encode($data)
        );

        return $this->client->getResponse();
    }

    protected function getProductModelFromInternalApi(string $productModelId): array
    {
        $this->client->request(
            'GET',
            sprintf('/enrich/product-model/rest/%s', $productModelId),
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ]
        );

        return json_decode($this->client->getResponse()->getContent(), true);
    }

    protected function createProductModel(array $fields = []): ProductModelInterface
    {
        $productModel = new ProductModel();
        $this->getProductModelUpdater()->update($productModel, $fields);
        $this->getProductModelSaver()->save($productModel);

        return $productModel;
    }


    protected function updateNormalizedProductModel(array $data, array $changes): array
    {
        unset($data['meta']);
        unset($data['family']);

        return array_merge_recursive($data, $changes);
    }

    protected function getProductModelSaver(): SaverInterface
    {
        return self::getContainer()->get('pim_catalog.saver.product_model');
    }

    protected function getProductModelUpdater(): ObjectUpdaterInterface
    {
        return self::getContainer()->get('pim_catalog.updater.product_model');
    }
}
