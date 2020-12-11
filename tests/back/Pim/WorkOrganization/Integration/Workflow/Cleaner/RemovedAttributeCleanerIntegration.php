<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AkeneoTestEnterprise\Pim\WorkOrganization\Integration\Workflow\Cleaner;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Cleaner\RemovedAttributeCleaner;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\DraftSource;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Test\Integration\TestCase;
use Ramsey\Uuid\Uuid;

class RemovedAttributeCleanerIntegration extends TestCase
{
    /**
     * @test
     */
    public function it_cleans_draft_containing_removed_attributes_values()
    {
        $attribute = $this->createAttributeTypeText('new_text_attribute');
        $this->createProductDraft('new_text_attribute');
        $this->createProductDraft('new_text_attribute');
        $this->createProductModelDraft('new_text_attribute');
        $this->createProductModelDraft('new_text_attribute');
        $this->removeAttribute($attribute);

        // We need to clear the cache because drafts created above are already in the unit of work
        // so the postLoad event is not triggered and thus the ExcludeDeletedAttributeSubscriber not called
        $this->get('pim_connector.doctrine.cache_clearer')->clear();

        $this->assertEquals(4, $this->countDraftWithRemovedAttributeValues());

        $this->getCleaner()->cleanAffectedDrafts();

        $this->assertEquals(0, $this->countDraftWithRemovedAttributeValues());
    }

    private function countDraftWithRemovedAttributeValues(): int
    {
        $findDraftIdsConcerningRemovedAttributesQuery = $this->get('pimee_workflow.query.find_draft_ids_concerning_removed_attributes');

        $productDraftCount = 0;
        foreach ($findDraftIdsConcerningRemovedAttributesQuery->forProducts() as $batch) {
            $productDraftCount += count($batch);
        }

        $productModelDraftCount = 0;
        foreach ($findDraftIdsConcerningRemovedAttributesQuery->forProductModels() as $batch) {
            $productModelDraftCount += count($batch);
        }

        return $productDraftCount + $productModelDraftCount;
    }

    private function getCleaner(): RemovedAttributeCleaner
    {
        return $this->get('pimee_workflow.cleaner.removed_attribute');
    }

    private function createAttributeTypeText(string $attributeCode): AttributeInterface
    {
        $attribute = $this->get('akeneo_integration_tests.base.attribute.builder')->build([
            'code' => $attributeCode,
            'type' => AttributeTypes::TEXT,
            'unique' => false,
            'group' => 'other',
            'localizable' => false
        ], true);

        $this->get('pim_catalog.saver.attribute')->save($attribute);

        return $attribute;
    }

    private function removeAttribute(AttributeInterface $attribute): void
    {
        $this->get('pim_catalog.remover.attribute')->remove($attribute);
    }

    private function createProductDraft(string $attributeCode): EntityWithValuesDraftInterface
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct(Uuid::uuid4()->toString());
        $this->get('pim_catalog.updater.product')->update($product, [
            'categories' => ['categoryA'],
            'values'     => [
                $attributeCode => [
                    ['data' => 'a text', 'locale' => null, 'scope' => null]
                ]
            ]
        ]);
        $this->get('pim_catalog.saver.product')->save($product);

        $this->get('pim_catalog.updater.product')->update($product, ['values' => [
            $attributeCode => [
                ['data' => 'an edited text', 'locale' => null, 'scope' => null]
            ]
        ]]);

        $productDraft = $this->get('pimee_workflow.product.builder.draft')
            ->build($product, new DraftSource('pim', 'PIM', 'mary', 'Mary Smith'));

        $this->get('pimee_workflow.saver.product_draft')->save($productDraft);

        return $productDraft;
    }

    private function createProductModelDraft(string $attributeCode): EntityWithValuesDraftInterface
    {
        $productModel = $this->get('pim_catalog.factory.product_model')->create();
        $this->get('pim_catalog.updater.product_model')->update(
            $productModel,
            [
                'code'           => Uuid::uuid4()->toString(),
                'family_variant' => 'familyVariantA2',
                'categories'     => ['categoryA'],
                'values'         => [
                    $attributeCode => [
                        ['data' => 'a text', 'locale' => null, 'scope' => null]
                    ]
                ]
            ]
        );

        $this->get('pim_catalog.saver.product_model')->save($productModel);
        $this->get('pim_catalog.updater.product_model')->update($productModel, ['values' => [
            $attributeCode => [
                ['data' => 'an edited text', 'locale' => null, 'scope' => null]
            ]
        ]]);

        $productModelDraft = $this->get('pimee_workflow.product_model.builder.draft')
            ->build($productModel, new DraftSource('pim', 'PIM', 'mary', 'Mary Smith'));

        $this->get('pimee_workflow.saver.product_model_draft')->save($productModelDraft);

        return $productModelDraft;
    }

    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
