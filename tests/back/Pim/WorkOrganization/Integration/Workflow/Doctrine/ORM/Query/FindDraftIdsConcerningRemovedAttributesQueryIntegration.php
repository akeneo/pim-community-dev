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

namespace AkeneoTestEnterprise\Pim\WorkOrganization\Integration\Workflow\Doctrine\ORM\Query;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\DraftSource;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Query\FindDraftIdsConcerningRemovedAttributesQueryInterface;
use Akeneo\Test\Integration\TestCase;
use Ramsey\Uuid\Uuid;

class FindDraftIdsConcerningRemovedAttributesQueryIntegration extends TestCase
{
    /**
     * @test
     */
    public function it_returns_an_empty_array_if_there_is_no_draft()
    {
        $productDraftIds = iterator_to_array($this->getQuery()->forProducts());
        $productModelDraftIds = iterator_to_array($this->getQuery()->forProductModels());

        $this->assertEmpty($productDraftIds);
        $this->assertEmpty($productModelDraftIds);
    }

    /**
     * @test
     */
    public function it_returns_product_draft_ids_only_of_drafts_containing_changes_on_deleted_attributes()
    {
        $attribute = $this->createAttributeTypeText('new_text_attribute');
        $productDraft1 = $this->createProductDraft('new_text_attribute');
        $productDraft2 = $this->createProductDraft('new_text_attribute');

        // This one should not appear in the query results
        $productDraft3 = $this->createProductDraft('a_text');

        $this->removeAttribute($attribute);

        $productDraftIds = [];
        foreach ($this->getQuery()->forProducts() as $batch) {
            foreach ($batch as $productDraftId) {
                $productDraftIds[] = $productDraftId;
            }
        }

        $this->assertEqualsCanonicalizing([
            $productDraft1->getId(),
            $productDraft2->getId(),
        ], $productDraftIds);
    }

    /**
     * @test
     */
    public function it_returns_product_model_draft_ids_only_of_drafts_containing_changes_on_deleted_attributes()
    {
        $attribute = $this->createAttributeTypeText('new_text_attribute');
        $productModelDraft1 = $this->createProductModelDraft('new_text_attribute');
        $productModelDraft2 = $this->createProductModelDraft('new_text_attribute');

        // This one should not appear in the query results
        $productModelDraft3 = $this->createProductModelDraft('a_text');

        $this->removeAttribute($attribute);

        $productModelDraftIds = [];
        foreach ($this->getQuery()->forProductModels() as $batch) {
            foreach ($batch as $productModelDraftId) {
                $productModelDraftIds[] = $productModelDraftId;
            }
        }

        $this->assertEqualsCanonicalizing([
            $productModelDraft1->getId(),
            $productModelDraft2->getId(),
        ], $productModelDraftIds);
    }

    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function getQuery(): FindDraftIdsConcerningRemovedAttributesQueryInterface
    {
        return $this->get('pimee_workflow.query.find_draft_ids_concerning_removed_attributes');
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
}
