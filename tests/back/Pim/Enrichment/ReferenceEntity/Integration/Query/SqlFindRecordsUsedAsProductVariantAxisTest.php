<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AkeneoTestEnterprise\Pim\Enrichment\ReferenceEntity\Integration\Query;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ChangeParent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetEnabled;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetSimpleReferenceEntityValue;
use Akeneo\Pim\Enrichment\ReferenceEntity\Bundle\Enrichment\SqlFindRecordsUsedAsProductVariantAxis;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\AttributeType\ReferenceEntityType;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\Structure\Component\Model\Family;
use Akeneo\Pim\Structure\Component\Model\FamilyVariant;
use Akeneo\Pim\Structure\Component\Model\VariantAttributeSet;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ChannelReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\LocaleReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\TextData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\Value;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ValueCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityRepositoryInterface;
use Akeneo\ReferenceEntity\Integration\SqlIntegrationTestCase;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Refresh;
use PHPUnit\Framework\Assert;

class SqlFindRecordsUsedAsProductVariantAxisTest extends SqlIntegrationTestCase
{
    private SqlFindRecordsUsedAsProductVariantAxis $findRecordsUsedAsProductVariantAxis;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resetDatabase();

        $this->findRecordsUsedAsProductVariantAxis = $this->get('akeneo_referenceentity.infrastructure.persistence.query.enrich.find_records_used_as_product_variant_axis');
        $catalog = $this->get('akeneo_integration_tests.catalogs');
        $fixturesLoader = $this->get('akeneo_integration_tests.loader.fixtures_loader');
        $fixturesLoader->load($catalog->useTechnicalCatalog());
        $this->get('pim_connector.doctrine.cache_clearer')->clear();

        $this->createReferenceEntity('designer');
        $attribute = $this->createAttributeReferenceEntitySingleLink(
            'a_reference_entity_single_link',
            'designer',
            'other'
        );
        $this->addAttributeToFamily($attribute, 'familyA');
        $this->createFamilyVariantWithAxis('familyA', $attribute, 'familyA_variant');
    }

    private function resetDatabase(): void
    {
        $this->get('akeneoreference_entity.tests.helper.database_helper')->resetDatabase();
    }

    /**
     * @test
     */
    public function it_returns_false_when_records_are_not_used_as_product_variant_axis()
    {
        $this->createRecord('stark');
        $this->createRecord('jambon');

        self::assertFalse($this->findRecordsUsedAsProductVariantAxis->areUsed(['stark', 'jambon'], 'designer'));
    }

    /**
     * @test
     */
    public function it_returns_true_when_record_are_used_as_product_variant_axis()
    {
        $this->createRecord('stark');
        $this->createRecord('jambon');
        $this->createProductVariantUsingRecordAsAxis('stark', 'stark-product-variant');
        $this->createProductVariantUsingRecordAsAxis('jambon', 'jambon-product-variant');

        self::assertTrue($this->findRecordsUsedAsProductVariantAxis->areUsed(['stark', 'jambon'], 'designer'));
    }

    /**
     * @test
     */
    public function it_returns_no_codes_when_records_are_not_used_as_product_variant_axis()
    {
        $this->createRecord('stark');
        $this->createRecord('jambon');

        self::assertEmpty($this->findRecordsUsedAsProductVariantAxis->getUsedCodes(['stark', 'jambon'], 'designer'));
    }

    /**
     * @test
     */
    public function it_returns_record_codes_used_as_product_variant_axis()
    {
        $this->createRecord('stark');
        $this->createRecord('jambon');
        $this->createRecord('jeanpaul');
        $this->createRecord('michel');
        $this->createProductVariantUsingRecordAsAxis('stark', 'stark-product-variant');
        $this->createProductVariantUsingRecordAsAxis('jambon', 'jambon-product-variant');

        self::assertEquals($this->findRecordsUsedAsProductVariantAxis->getUsedCodes(
            [
                'stark',
                'jambon',
                'jeanpaul',
                'michel',
            ],
            'designer'
        ), [
            'stark',
            'jambon',
        ]);
    }

    private function createProductVariantUsingRecordAsAxis(string $recordCode, string $productVariantCode): void
    {
        $productModel = $this->createProductModel('familyA_variant', $productVariantCode . '-product-model');
        $this->createProductVariant(
            $productVariantCode,
            [
                new SetFamily('familyA'),
                new SetEnabled(true),
                new ChangeParent($productModel->getCode()),
                new SetSimpleReferenceEntityValue(
                    'a_reference_entity_single_link',
                    null,
                    null,
                    $recordCode
                )
            ]
        );
    }

    private function createReferenceEntity(string $identifier): ReferenceEntity
    {
        $referenceEntityRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity');

        $referenceEntity = ReferenceEntity::create(
            ReferenceEntityIdentifier::fromString($identifier),
            ['en_US' => $identifier],
            Image::createEmpty(),
        );
        $referenceEntityRepository->create($referenceEntity);

        return $referenceEntity;
    }

    private function createRecord(string $code): Record
    {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');

        /** @var ReferenceEntityRepositoryInterface $referenceEntityRepository */
        $referenceEntityRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity');
        $referenceEntity = $referenceEntityRepository->getByIdentifier($referenceEntityIdentifier);

        $recordRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.record');

        $recordCode = RecordCode::fromString($code);
        $record = Record::create(
            $recordRepository->nextIdentifier($referenceEntityIdentifier, $recordCode),
            $referenceEntityIdentifier,
            $recordCode,
            ValueCollection::fromValues([
                Value::create(
                    $referenceEntity->getAttributeAsLabelReference()->getIdentifier(),
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
                    TextData::fromString($code)
                ),
            ])
        );
        $recordRepository->create($record);

        return $record;
    }

    private function findFamily(string $code): Family
    {
        $familyRepository = $this->get('pim_catalog.repository.family');

        return $familyRepository->findOneByIdentifier($code);
    }

    private function findFamilyVariant(string $code): FamilyVariant
    {
        $familyVariantRepository = $this->get('pim_catalog.repository.family_variant');

        return $familyVariantRepository->findOneByIdentifier($code);
    }

    private function createProductModel(string $familyVariantCode, string $code): ProductModel
    {
        $productModelUpdater = $this->get('pim_catalog.updater.product_model');

        $productModel = new ProductModel();
        $productModelUpdater->update($productModel, [
            'code' => $code,
        ]);

        $productModel->setFamilyVariant($this->findFamilyVariant($familyVariantCode));

        $this->get('pim_catalog.saver.product_model')->save($productModel);
        $this->get('pim_catalog.elasticsearch.indexer.product_model')->indexFromProductModelCode($code, [
            'index_refresh' => Refresh::enable(),
        ]);

        return $productModel;
    }

    private function createProductVariant(string $identifier, array $userIntents): ProductInterface
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

        return $this->get('pim_catalog.repository.product')->findOneByIdentifier($identifier);
    }

    private function createFamilyVariantWithAxis(string $familyCode, Attribute $attribute, string $code): FamilyVariant
    {
        $family = $this->findFamily($familyCode);
        $variantAttributeSet = new VariantAttributeSet();
        $variantAttributeSet->setLevel(1);
        $variantAttributeSet->addAttribute($attribute);
        $variantAttributeSet->setAxes([$attribute]);

        $familyVariant = new FamilyVariant();
        $familyVariant->setCode($code);
        $familyVariant->setFamily($family);
        $familyVariant->addVariantAttributeSet($variantAttributeSet);

        $this->get('pim_catalog.saver.family_variant')->save($familyVariant);

        return $familyVariant;
    }

    private function createAttributeReferenceEntitySingleLink(
        string $code,
        string $referenceEntityIdentifier,
        string $attributeGroupCode
    ): Attribute {
        $attributeReferenceEntity = $this->get('pim_catalog.factory.attribute')
            ->createAttribute(ReferenceEntityType::REFERENCE_ENTITY);
        $this->get('pim_catalog.updater.attribute')
            ->update($attributeReferenceEntity, [
                'code' => $code,
                'reference_data_name' => $referenceEntityIdentifier,
                'group' => $attributeGroupCode,
            ]);

        $this->get('pim_catalog.saver.attribute')->save($attributeReferenceEntity);

        return $attributeReferenceEntity;
    }

    private function addAttributeToFamily(Attribute $attribute, string $familyCode): void
    {
        $family = $this->findFamily($familyCode);
        $family->addAttribute($attribute);
        $this->get('pim_catalog.saver.family')->save($family);
    }

    private function clearDoctrineUoW(): void
    {
        $this->get('pim_connector.doctrine.cache_clearer')->clear();
    }

    private function getUserId(string $username): int
    {
        $query = <<<SQL
            SELECT id FROM oro_user WHERE username = :username
        SQL;
        $stmt = $this->get('database_connection')->executeQuery($query, ['username' => $username]);
        $id = $stmt->fetchOne();
        Assert::assertNotNull($id);

        return \intval($id);
    }
}
