<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ApiBundle\tests\integration\Controller\ProductProposal;

use Akeneo\Test\Integration\Configuration;
use Pim\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Component\Workflow\Model\EntityWithValuesDraftInterface;

class AbstractProposalTestCase extends ApiTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    /**
     * @param string $identifier
     * @param array  $data
     *
     * @return ProductInterface
     */
    protected function createProduct(string $identifier, array $data = []): ProductInterface
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier);

        $this->updateProduct($product, $data);

        return $product;
    }

    /**
     * @param string $identifier
     * @param array  $data
     *
     * @return ProductInterface
     */
    protected function createVariantProduct(string $identifier, array $data = []): ProductInterface
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier);

        $this->updateProduct($product, $data);

        return $product;
    }

    /**
     * @param ProductInterface $product
     * @param array            $data
     */
    protected function updateProduct(ProductInterface $product, array $data): void
    {
        $this->get('pim_catalog.updater.product')->update($product, $data);
        $this->get('pim_catalog.saver.product')->save($product);

        $this->get('akeneo_elasticsearch.client.product')->refreshIndex();
    }

    /**
     * @param string           $userName
     * @param ProductInterface $product
     * @param array            $changes
     *
     * @return EntityWithValuesDraftInterface
     */
    protected function createEntityWithValuesDraft(string $userName, ProductInterface $product, array $changes): EntityWithValuesDraftInterface
    {
        $this->get('pim_catalog.updater.product')->update($product, $changes);

        $productDraft = $this->get('pimee_workflow.product.builder.draft')->build($product, $userName);

        $this->get('pimee_workflow.saver.product_draft')->save($productDraft);

        return $productDraft;
    }
}
