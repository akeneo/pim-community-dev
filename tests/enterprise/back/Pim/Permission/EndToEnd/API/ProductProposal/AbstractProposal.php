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

namespace AkeneoTestEnterprise\Pim\Permission\EndToEnd\API\ProductProposal;

use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductIdentifier;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;

class AbstractProposal extends ApiTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog(['proposal', 'permission']);
    }

    /**
     * @param UserIntent[]  $userIntents
     */
    protected function createProduct(string $identifier, array $userIntents = [], string $userName = 'admin'): ProductInterface
    {
        return $this->createOrUpdateProduct($identifier, $userIntents, $userName);
    }

    protected function refreshIndex(): void
    {
        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();
        $this->get('pim_catalog.validator.unique_value_set')->reset();
    }

    /**
     * @param UserIntent[]  $userIntents
     */
    protected function createVariantProduct(string $identifier, array $userIntents = [], $userName): ProductInterface
    {
        return $this->createProduct($identifier, $userIntents, $userName);
    }

    /**
     * @param ProductInterface $product
     * @param UserIntent[] $userIntents
     */
    protected function updateProduct(ProductInterface $product, array $userIntents = [], string $userName = 'admin'): void
    {
        $this->createOrUpdateProduct($product->getIdentifier(), $userIntents, $userName);
    }

    private function createOrUpdateProduct(string $identifier, array $userIntents = [], string $userName = 'admin'): ProductInterface
    {
        $command = UpsertProductCommand::createWithIdentifier(
            userId: $this->getUserId($userName),
            productIdentifier: ProductIdentifier::fromIdentifier($identifier),
            userIntents: $userIntents
        );
        $this->get('pim_enrich.product.message_bus')->dispatch($command);

        $this->refreshIndex();

        return $this->get('pim_catalog.repository.product')->findOneByIdentifier($identifier);
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

        $user = $this->get('pim_user.provider.user')->loadUserByUsername($userName);
        $draftSource = $this->get('Akeneo\Pim\WorkOrganization\Workflow\Component\Factory\PimUserDraftSourceFactory')->createFromUser($user);
        $productDraft = $this->get('pimee_workflow.product.builder.draft')->build($product, $draftSource);

        $this->get('pimee_workflow.saver.product_draft')->save($productDraft);

        return $productDraft;
    }
}
