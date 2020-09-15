<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Enrichment\Integration;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\AttributeType\ReferenceEntityCollectionType;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\AttributeType\ReferenceEntityType;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ValueCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

class EmptyFilterWithProductsAndProductModelsIntegration extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->withReferenceEntity('nice_reference_entity');
        $this->withRecords('nice_reference_entity', 'super_record', 'not_so_nice_record');
        $this->withAttributes([
            'a_reference_entity_attribute' => [
                'type' => ReferenceEntityType::REFERENCE_ENTITY,
                'reference_entity' => 'nice_reference_entity'
            ],
            'a_reference_entity_collection_attribute' => [
                'type' => ReferenceEntityCollectionType::REFERENCE_ENTITY_COLLECTION,
                'reference_entity' => 'nice_reference_entity'
            ]
        ]);

    }

    public function testEmptyOperatorForReferenceEntityFilter()
    {
        $this->loadFixtures(
            'a_reference_entity_attribute',
            ['data' => 'super_record', 'scope' => null, 'locale' => null]
        );
        $this->assert('a_reference_entity_attribute');
    }

    public function testEmptyOperatorForReferenceEntityCollectionFilter()
    {
        $this->loadFixtures(
            'a_reference_entity_collection_attribute',
            ['data' => ['super_record', 'not_so_nice_record'], 'scope' => null, 'locale' => null]
        );
        $this->assert('a_reference_entity_collection_attribute');
    }

    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function assert(string $attributeCode)
    {
        $pqb = $this->get('pim_catalog.query.product_and_product_model_query_builder_factory')->create();
        $pqb->addFilter($attributeCode, Operators::IS_EMPTY, null);
        $results = $pqb->execute();
        $identifiers = [];
        foreach ($results as $entity) {
            $identifiers[] = $entity instanceof ProductModelInterface ? $entity->getCode() : $entity->getIdentifier();
        }
        Assert::assertEqualsCanonicalizing(
            ['pm_1_empty', 'variant_1_empty', 'variant_3_empty', 'simple_product_empty'],
            $identifiers
        );
    }

    /**
     * Creates
     * - a family with the given attribute code
     * - a family variant with the given attribute at root level, and another one with the attribute at variant level
     * - foreach family variant, product models and variant products with empty or filled value for the given attribute
     * - 2 simple products, one with empty value, one with non empty value
     * - a simple product without family
     */
    private function loadFixtures(string $attributeCode, array $nonEmptyData)
    {
        $this->createFamily([
            'code' => 'a_family',
            'attributes' => [$attributeCode, 'sku', 'a_yes_no', 'a_number_float_negative']
        ]);
        $this->createFamilyVariant([
            'code' => 'attribute_at_common_level',
            'family' => 'a_family',
            'variant_attribute_sets' => [
                [
                    'level' => 1,
                    'axes' => ['a_yes_no'],
                    'attributes' => ['sku', 'a_yes_no']
                ]
            ]
        ]);
        $this->createProductModel([
            'code' => 'pm_1_empty',
            'family_variant' => 'attribute_at_common_level',
        ]);
        $this->createProduct('variant_1_empty', [
            'parent' => 'pm_1_empty',
            'values' => [
                'a_yes_no' => [['data' => true, 'scope' => null, 'locale' => null]],
            ]
        ]);
        $this->createProductModel([
            'code' => 'pm_2_filled',
            'family_variant' => 'attribute_at_common_level',
            'values' => [
                $attributeCode => [$nonEmptyData],
            ]
        ]);
        $this->createProduct('variant_2_filled', [
            'parent' => 'pm_2_filled',
            'values' => [
                'a_yes_no' => [['data' => true, 'scope' => null, 'locale' => null]],
            ]
        ]);

        $this->createFamilyVariant([
            'code' => 'attribute_at_variant_level',
            'family' => 'a_family',
            'variant_attribute_sets' => [
                [
                    'level' => 1,
                    'axes' => ['a_yes_no'],
                    'attributes' => ['sku', 'a_yes_no', $attributeCode]
                ]
            ]
        ]);

        $this->createProductModel([
            'code' => 'pm_3',
            'family_variant' => 'attribute_at_variant_level',
        ]);

        $this->createProduct('variant_3_empty', [
            'parent' => 'pm_3',
            'values' => [
                'a_yes_no' => [['data' => true, 'scope' => null, 'locale' => null]],
            ]
        ]);
        $this->createProduct('variant_3_filled', [
            'parent' => 'pm_3',
            'values' => [
                'a_yes_no' => [['data' => false, 'scope' => null, 'locale' => null]],
                $attributeCode => [$nonEmptyData],
            ]
        ]);

        $this->createProduct('simple_product_empty', [
            'family' => 'a_family',
        ]);
        $this->createProduct('simple_product_filled', [
            'family' => 'a_family',
            'values' => [
                $attributeCode => [$nonEmptyData],
            ]
        ]);
        $this->createProduct('simple_product_without_family', []);

        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();
    }

    private function withReferenceEntity(string $identifier): void
    {
        $referenceEntityRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity');
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString($identifier);
        $referenceEntityRepository->create(
            ReferenceEntity::create(
                $referenceEntityIdentifier,
                [],
                Image::createEmpty())
        );
    }

    private function withRecords(string $referenceEntityIdentifier, string ...$records)
    {
        $identifier = ReferenceEntityIdentifier::fromString($referenceEntityIdentifier);
        $recordRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.record');

        foreach ($records as $record) {
            $recordRepository->create(
                Record::create(
                    RecordIdentifier::fromString($record),
                    $identifier,
                    RecordCode::fromString($record),
                    ValueCollection::fromValues([])
                )
            );
        }
    }

    private function withAttributes(array $attributesData): void
    {
        $attributes = [];
        foreach ($attributesData as $attributeCode => $attributeInfo) {
            $data = [
                'code' => $attributeCode,
                'type' => $attributeInfo['type'],
                'localizable' => false,
                'scopable' => false,
                'group' => 'other',
            ];
            /** @var AttributeInterface $attribute */
            $attribute = $this->get('pim_catalog.factory.attribute')->create();
            $attribute->setProperty('reference_data_name', $attributeInfo['reference_entity']);
            $this->get('pim_catalog.updater.attribute')->update($attribute, $data);

            $constraints = $this->get('validator')->validate($attribute);

            Assert::assertCount(0, $constraints);
            $attributes[] = $attribute;
        }

        $this->get('pim_catalog.saver.attribute')->saveAll($attributes);
    }

    private function createFamily(array $data): void
    {
        $family = $this->get('pim_catalog.factory.family')->create();
        $this->get('pim_catalog.updater.family')->update($family, $data);
        Assert::assertEmpty($this->get('validator')->validate($family));
        $this->get('pim_catalog.saver.family')->save($family);
    }

    private function createFamilyVariant($data): void
    {
        $familyVariant = $this->get('pim_catalog.factory.family_variant')->create();
        $this->get('pim_catalog.updater.family_variant')->update($familyVariant, $data);
        Assert::assertEmpty($this->get('validator')->validate($familyVariant));
        $this->get('pim_catalog.saver.family_variant')->save($familyVariant);
    }

    private function createProduct(string $identifier, array $data): void
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier);
        $this->get('pim_catalog.updater.product')->update($product, $data);
        $violations = $this->get('pim_catalog.validator.product')->validate($product);
        Assert::assertEmpty($violations, sprintf('The %s product is not valid: %s', $product->getIdentifier(), $violations));
        $this->get('pim_catalog.saver.product')->save($product);
    }

    private function createProductModel(array $data): void
    {
        $productModel = $this->get('pim_catalog.factory.product_model')->create();
        $this->get('pim_catalog.updater.product_model')->update($productModel, $data);
        $violations = $this->get('pim_catalog.validator.product_model')->validate($productModel);
        Assert::assertEmpty($violations, sprintf('The %s product model is not valid: %s', $productModel->getCode(), $violations));
        $this->get('pim_catalog.saver.product_model')->save($productModel);
    }
}
