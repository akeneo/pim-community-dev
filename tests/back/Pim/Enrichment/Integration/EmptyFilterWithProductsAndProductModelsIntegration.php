<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Enrichment\Integration;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ChangeParent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetBooleanValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMultiReferenceEntityValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetSimpleReferenceEntityValue;
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
        $this->get('feature_flags')->enable('reference_entity');

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
        $this->createProduct(
            'variant_1_empty',
            [
                new ChangeParent('pm_1_empty'),
                new SetBooleanValue('a_yes_no', null, null, true)
            ]
        );
        $this->createProductModel([
            'code' => 'pm_2_filled',
            'family_variant' => 'attribute_at_common_level',
            'values' => [
                $attributeCode => [$nonEmptyData],
            ]
        ]);
        $this->createProduct(
            'variant_2_filled',
            [
                new ChangeParent('pm_2_filled'),
                new SetBooleanValue('a_yes_no', null, null, true)
            ]
        );

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


        // todo
        $refEntityUserIntent = $this->createReferenceEntityUserIntentFromAttributeValue(
            $attributeCode,
            $nonEmptyData
        );
        $this->createProduct(
            'variant_3_empty',
            [
                new ChangeParent('pm_3'),
                new SetBooleanValue('a_yes_no', null, null, true)
            ]
        );
        $this->createProduct(
            'variant_3_filled',
            [
                new ChangeParent('pm_3'),
                new SetBooleanValue('a_yes_no', null, null, false),
                $refEntityUserIntent
            ]
        );

        $this->createProduct('simple_product_empty', [new SetFamily('a_family')]);
        $this->createProduct(
            'simple_product_filled',
            [
                new SetFamily('a_family'),
                $refEntityUserIntent
            ]
        );
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

    private function createProduct(string $identifier, array $userIntents): void
    {
        $command = UpsertProductCommand::createFromCollection(
            userId: $this->getUserId('admin'),
            productIdentifier: $identifier,
            userIntents: $userIntents
        );
        $this->get('pim_enrich.product.message_bus')->dispatch($command);
        $this->getContainer()->get('pim_catalog.validator.unique_value_set')->reset();
        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();
        $this->clearDoctrineUoW();
    }

    private function createProductModel(array $data): void
    {
        $productModel = $this->get('pim_catalog.factory.product_model')->create();
        $this->get('pim_catalog.updater.product_model')->update($productModel, $data);
        $violations = $this->get('pim_catalog.validator.product_model')->validate($productModel);
        Assert::assertEmpty($violations, sprintf('The %s product model is not valid: %s', $productModel->getCode(), $violations));
        $this->get('pim_catalog.saver.product_model')->save($productModel);
    }

    protected function clearDoctrineUoW(): void
    {
        $this->get('pim_connector.doctrine.cache_clearer')->clear();
    }

    protected function getUserId(string $username): int
    {
        $query = <<<SQL
            SELECT id FROM oro_user WHERE username = :username
        SQL;
        $stmt = $this->get('database_connection')->executeQuery($query, ['username' => $username]);
        $id = $stmt->fetchOne();
        Assert::assertNotNull($id);

        return \intval($id);
    }

    /**
     *
     * ['data' => 'super_record', 'scope' => null, 'locale' => null]
     * ['data' => ['super_record', 'not_so_nice_record'], 'scope' => null, 'locale' => null]
     *
     *
     * @param string $attributeCode
     * @param array $standardFormat
     * @return void
     */
    private function createReferenceEntityUserIntentFromAttributeValue(
        string $attributeCode,
        array $standardFormat
    ): SetSimpleReferenceEntityValue|SetMultiReferenceEntityValue
    {
        Assert::assertArrayHasKey( 'data', $standardFormat, '"data" key missing in standard format');

        $userIntent = null;
        if (\is_string($standardFormat['data'])) {
            $userIntent = new SetSimpleReferenceEntityValue(
                $attributeCode,
                $standardFormat['scope'] ?? null,
                $standardFormat['locale'] ?? null,
                $standardFormat['data']
            );
        } elseif (\is_array($standardFormat['data'])) {
            $userIntent = new SetMultiReferenceEntityValue(
                $attributeCode,
                $standardFormat['scope'] ?? null,
                $standardFormat['locale'] ?? null,
                $standardFormat['data']
            );
        }

        Assert::assertNotNull($userIntent, 'data must either be a string or an array of string');
        return $userIntent;
    }
}
