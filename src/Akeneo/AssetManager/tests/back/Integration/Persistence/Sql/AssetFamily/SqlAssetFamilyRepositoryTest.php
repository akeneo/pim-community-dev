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

namespace Akeneo\AssetManager\Integration\Persistence\Sql\AssetFamily;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplateCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation\ThumbnailOperation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\OperationCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Source;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Target;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Transformation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\TransformationLabel;
use Akeneo\AssetManager\Domain\Model\AssetFamily\TransformationCollection;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsReadOnly;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\AssetManager\Domain\Model\Attribute\TextAttribute;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyNotFoundException;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\AssetManager\Integration\SqlIntegrationTestCase;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use Doctrine\DBAL\DBALException;

class SqlAssetFamilyRepositoryTest extends SqlIntegrationTestCase
{
    private AssetFamilyRepositoryInterface $repository;

    private AttributeRepositoryInterface $attributeRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family');
        $this->attributeRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.attribute');
        $this->resetDB();
    }

    /**
     * @test
     */
    public function it_creates_an_asset_family_and_returns_it()
    {
        $ruleTemplate = $this->getRuleTemplate();
        $identifier = AssetFamilyIdentifier::fromString('identifier');
        $assetFamily = AssetFamily::create(
            $identifier,
            ['en_US' => 'Designer', 'fr_FR' => 'Concepteur'],
            Image::createEmpty(),
            RuleTemplateCollection::createFromProductLinkRules([$ruleTemplate])
        );

        $this->repository->create($assetFamily);

        $assetFamilyFound = $this->repository->getByIdentifier($identifier);
        $this->assertAssetFamily($assetFamily, $assetFamilyFound);
    }

    /**
     * @test
     */
    public function it_returns_all_asset_families()
    {
        $designer = AssetFamily::create(
            AssetFamilyIdentifier::fromString('designer'),
            ['en_US' => 'Designer'],
            Image::createEmpty(),
            RuleTemplateCollection::empty()
        );
        $brand = AssetFamily::create(
            AssetFamilyIdentifier::fromString('brand'),
            ['en_US' => 'Brand'],
            Image::createEmpty(),
            RuleTemplateCollection::empty()
        );
        $this->repository->create($designer);
        $this->repository->create($brand);

        $assetFamilies = iterator_to_array($this->repository->all());
        $this->assertAssetFamily($brand, $assetFamilies[0]);
        $this->assertAssetFamily($designer, $assetFamilies[1]);
    }

    /**
     * @test
     */
    public function it_throws_when_creating_an_asset_family_with_the_same_identifier()
    {
        $identifier = AssetFamilyIdentifier::fromString('identifier');
        $assetFamily = AssetFamily::create(
            $identifier,
            ['en_US' => 'Designer', 'fr_FR' => 'Concepteur'],
            Image::createEmpty(),
            RuleTemplateCollection::empty()
        );
        $this->repository->create($assetFamily);

        $this->expectException(DBALException::class);
        $this->repository->create($assetFamily);
    }

    /**
     * @test
     */
    public function it_updates_an_asset_family_and_returns_it()
    {
        $identifier = AssetFamilyIdentifier::fromString('identifier');
        $assetFamily = AssetFamily::create(
            $identifier,
            ['en_US' => 'Designer', 'fr_FR' => 'Concepteur'],
            Image::createEmpty(),
            RuleTemplateCollection::empty()
        );
        $this->repository->create($assetFamily);
        $assetFamily->updateLabels(LabelCollection::fromArray(['en_US' => 'Stylist', 'fr_FR' => 'Styliste']));

        $file = new FileInfo();
        $file->setKey('/path/image.jpg');
        $file->setOriginalFilename('image.jpg');
        $assetFamily->updateImage(Image::fromFileInfo($file));

        $this->repository->update($assetFamily);

        $assetFamilyFound = $this->repository->getByIdentifier($identifier);
        $this->assertAssetFamily($assetFamily, $assetFamilyFound);
    }

    /**
     * @test
     */
    public function it_updates_a_family_with_transformations_and_returns_it()
    {
        $identifier = AssetFamilyIdentifier::fromString('identifier');
        $assetFamily = AssetFamily::create(
            $identifier,
            ['en_US' => 'Designer', 'fr_FR' => 'Concepteur'],
            Image::createEmpty(),
            RuleTemplateCollection::empty()
        );
        $this->repository->create($assetFamily);

        $assetFamily = $assetFamily->withTransformationCollection($this->getTransformationCollection());
        $this->repository->update($assetFamily);

        $assetFamilyFound = $this->repository->getByIdentifier($identifier);
        $this->assertAssetFamily($assetFamily, $assetFamilyFound);
    }

    /**
     * @test
     */
    public function it_throws_if_the_identifier_is_not_found()
    {
        $this->expectException(AssetFamilyNotFoundException::class);
        $this->repository->getByIdentifier(AssetFamilyIdentifier::fromString('unknown_identifier'));
    }

    /**
     * @test
     */
    public function it_deletes_an_asset_family_given_an_identifier()
    {
        $identifier = AssetFamilyIdentifier::fromString('identifier');
        $assetFamily = AssetFamily::create(
            $identifier,
            ['en_US' => 'Designer', 'fr_FR' => 'Concepteur'],
            Image::createEmpty(),
            RuleTemplateCollection::empty()
        );
        $this->repository->create($assetFamily);

        $this->repository->deleteByIdentifier($identifier);

        $this->expectException(AssetFamilyNotFoundException::class);
        $this->repository->getByIdentifier($identifier);
    }

    /**
     * @test
     */
    public function it_deletes_an_asset_family_given_an_identifier_even_if_it_has_attributes()
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');
        $assetFamily = AssetFamily::create(
            $assetFamilyIdentifier,
            ['en_US' => 'Designer', 'fr_FR' => 'Concepteur'],
            Image::createEmpty(),
            RuleTemplateCollection::empty()
        );
        $this->repository->create($assetFamily);

        $identifier = AttributeIdentifier::create('designer', 'name', 'test');
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');
        $attribute = TextAttribute::createText(
            $identifier,
            $assetFamilyIdentifier,
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['en_US' => 'Name', 'fr_FR' => 'Nom']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxLength::fromInteger(255),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );
        $this->attributeRepository->create($attribute);

        $this->repository->deleteByIdentifier($assetFamilyIdentifier);

        $this->expectException(AssetFamilyNotFoundException::class);
        $this->repository->getByIdentifier($assetFamilyIdentifier);
    }

    /**
     * @test
     */
    public function it_counts_all_asset_families()
    {
        $this->assertEquals(0, $this->repository->count());

        $designerIdentifier = AssetFamilyIdentifier::fromString('designer');
        $designer = AssetFamily::create(
            $designerIdentifier,
            ['en_US' => 'Designer', 'fr_FR' => 'Concepteur'],
            Image::createEmpty(),
            RuleTemplateCollection::empty()
        );
        $brandIdentifier = AssetFamilyIdentifier::fromString('brand');
        $brand = AssetFamily::create(
            $brandIdentifier,
            ['en_US' => 'Brand', 'fr_FR' => 'Marque'],
            Image::createEmpty(),
            RuleTemplateCollection::empty()
        );

        $this->repository->create($designer);
        $this->repository->create($brand);

        $this->assertEquals(2, $this->repository->count());
    }

    /**
     * @test
     */
    public function it_throws_an_exception_if_it_tries_to_delete_an_unknown_asset_family()
    {
        $identifier = AssetFamilyIdentifier::fromString('unknown');

        $this->expectException(AssetFamilyNotFoundException::class);
        $this->repository->deleteByIdentifier($identifier);
    }

    /**
     * @param $assetFamilyExpected
     * @param $assetFamilyFound
     */
    private function assertAssetFamily(
        AssetFamily $assetFamilyExpected,
        AssetFamily $assetFamilyFound
    ): void {
        $this->assertTrue($assetFamilyExpected->equals($assetFamilyFound));
        $labelCodesExpected = $assetFamilyExpected->getLabelCodes();
        $labelCodesFound = $assetFamilyFound->getLabelCodes();
        sort($labelCodesExpected);
        sort($labelCodesFound);
        $this->assertSame($labelCodesExpected, $labelCodesFound);
        foreach ($assetFamilyExpected->getLabelCodes() as $localeCode) {
            $this->assertEquals($assetFamilyExpected->getLabel($localeCode),
                                $assetFamilyFound->getLabel($localeCode));
        }

        $this->assertEquals(
            $assetFamilyExpected->getTransformationCollection(),
            $assetFamilyFound->getTransformationCollection()
        );
    }

    private function getRuleTemplate(): array
    {
        return [
            'product_selections' => [
                [
                    'field'    => 'sku',
                    'operator' => '=',
                    'value'    => '{{product_sku}}'
                ]
            ],
            'assign_assets_to' => [
                [
                    'mode'      => 'replace',
                    'attribute' => '{{attribute}}'
                ]
            ]
        ];
    }

    private function getTransformationCollection(): TransformationCollection
    {
        return TransformationCollection::create(
            [
                Transformation::create(
                    TransformationLabel::fromString('label'),
                    Source::createFromNormalized(['attribute' => 'main_image', 'channel' => null, 'locale' => null]),
                    Target::createFromNormalized(['attribute' => 'thumbnail', 'channel' => null, 'locale' => null]),
                    OperationCollection::create([ThumbnailOperation::create(['width' => 100, 'height' => 80])]),
                    '1_',
                    '_2',
                    new \DateTime('1990-01-01')
                ),
            ]
        );
    }

    private function resetDB()
    {
        $this->get('akeneoasset_manager.tests.helper.database_helper')->resetDatabase();
    }
}
