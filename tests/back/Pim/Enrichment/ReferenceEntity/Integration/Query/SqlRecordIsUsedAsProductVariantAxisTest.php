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

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\ReferenceEntity\Bundle\Enrichment\SqlRecordIsUsedAsProductVariantAxis;
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
use Webmozart\Assert\Assert;

class SqlRecordIsUsedAsProductVariantAxisTest extends SqlIntegrationTestCase
{
    /** @var SqlRecordIsUsedAsProductVariantAxis */
    private $recordIsUsedAsProductVariantAxis;

    protected function setUp(): void
    {
        parent::setUp();

        $this->recordIsUsedAsProductVariantAxis = $this->get('akeneo_referenceentity.infrastructure.persistence.query.enrich.record_is_used_as_product_variant_axis');
        $catalog = $this->get('akeneo_integration_tests.catalogs');
        $fixturesLoader = $this->get('akeneo_integration_tests.loader.fixtures_loader');
        $fixturesLoader->load($catalog->useTechnicalCatalog());
        $this->get('pim_connector.doctrine.cache_clearer')->clear();
    }

    private function resetDB(): void
    {
        $this->get('akeneoreference_entity.tests.helper.database_helper')->resetDatabase();
    }

    /**
     * @test
     */
    public function it_return_false_when_record_is_not_used_as_product_variant_axis()
    {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $recordCode = RecordCode::fromString('stark');

        Assert::same($this->recordIsUsedAsProductVariantAxis->execute($recordCode, $referenceEntityIdentifier), false);
    }

    /**
     * @test
     */
    public function it_return_true_when_record_is_used_as_product_variant_axis()
    {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $recordCode = RecordCode::fromString('stark');

        $this->createProductVariantUsingReferenceEntityRecordAsAxis(
            $referenceEntityIdentifier,
            $recordCode
        );

        Assert::same($this->recordIsUsedAsProductVariantAxis->execute($recordCode, $referenceEntityIdentifier), true);
    }

    private function createProductVariantUsingReferenceEntityRecordAsAxis(
        ReferenceEntityIdentifier $referenceEntityIdentifier,
        RecordCode $recordCode
    ): void {
        $referenceEntity = $this->createReferenceEntity((string)$referenceEntityIdentifier);
        $this->createReferenceEntityRecord($referenceEntity, (string)$recordCode);

        $family = $this->findFamily('familyA');
        $attribute = $this->createAttributeReferenceEntitySingleLink('a_reference_entity_single_link', $referenceEntity,
            'other');
        $this->addAttributeToFamily($family, $attribute);
        $familyVariant = $this->createFamilyVariant($family, $attribute, 'a_reference_entity_single_link');

        $productModel = $this->createProductModel($familyVariant, 'jacket');
        $this->createProductVariant('jacket-stark', $family, [
            'enabled' => true,
            'parent' => $productModel->getCode(),
            'values' => [
                'a_reference_entity_single_link' => [
                    [
                        'data' => (string)$recordCode,
                        'scope' => null,
                        'locale' => null,
                    ],
                ],
            ],
        ]);
    }

    private function createReferenceEntity(string $identifier): ReferenceEntity
    {
        $referenceEntityRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity');

        $referenceEntity = ReferenceEntity::create(
            ReferenceEntityIdentifier::fromString('designer'),
            ['en_US' => $identifier],
            Image::createEmpty()
        );
        $referenceEntityRepository->create($referenceEntity);

        return $referenceEntity;
    }

    private function createReferenceEntityRecord(ReferenceEntity $referenceEntity, string $code): Record
    {
        $referenceEntityIdentifier = $referenceEntity->getIdentifier();

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

    private function createProductModel(FamilyVariant $familyVariant, string $code): ProductModel
    {
        $productModelUpdater = $this->get('pim_catalog.updater.product_model');

        $productModel = new ProductModel();
        $productModelUpdater->update($productModel, [
            'code' => $code,
        ]);

        $productModel->setFamilyVariant($familyVariant);

        $this->get('pim_catalog.saver.product_model')->save($productModel);
        $this->get('pim_catalog.elasticsearch.indexer.product_model')->indexFromProductModelCode($code, [
            'index_refresh' => Refresh::enable(),
        ]);

        return $productModel;
    }

    private function createProductVariant(
        string $identifier,
        Family $family,
        array $values
    ): Product {
        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier, $family->getCode());

        $this->get('pim_catalog.updater.product')->update($product, $values);
        $this->get('pim_catalog.saver.product')->save($product);
        $this->get('pim_catalog.elasticsearch.indexer.product')->indexFromProductIdentifier($identifier, [
            'index_refresh' => Refresh::enable(),
        ]);

        return $product;
    }

    private function addAttributeToFamily(Family $family, Attribute $attribute): void
    {
        $family->addAttribute($attribute);

        $this->get('pim_catalog.saver.family')->save($family);
    }

    private function createFamilyVariant(Family $family, Attribute $attribute, string $code): FamilyVariant
    {
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
        ReferenceEntity $referenceEntity,
        string $attributeGroupCode
    ): Attribute {
        $attributeReferenceEntity = $this->get('pim_catalog.factory.attribute')
            ->createAttribute(ReferenceEntityType::REFERENCE_ENTITY);
        $this->get('pim_catalog.updater.attribute')
            ->update($attributeReferenceEntity, [
                'code' => $code,
                'reference_data_name' => (string)$referenceEntity->getIdentifier(),
                'group' => $attributeGroupCode,
            ]);

        $this->get('pim_catalog.saver.attribute')->save($attributeReferenceEntity);

        return $attributeReferenceEntity;
    }
}
