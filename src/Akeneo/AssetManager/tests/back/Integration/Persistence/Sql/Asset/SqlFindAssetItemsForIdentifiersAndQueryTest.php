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

namespace Akeneo\AssetManager\Integration\Persistence\Sql\Asset;

use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ChannelReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\LocaleReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\TextData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\Value;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ValueCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplateCollection;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifier;
use Akeneo\AssetManager\Domain\Query\Asset\AssetItem;
use Akeneo\AssetManager\Domain\Query\Asset\AssetQuery;
use Akeneo\AssetManager\Domain\Query\Asset\FindAssetItemsForIdentifiersAndQueryInterface;
use Akeneo\AssetManager\Integration\SqlIntegrationTestCase;

class SqlFindAssetItemsForIdentifiersAndQueryTest extends SqlIntegrationTestCase
{
    private FindAssetItemsForIdentifiersAndQueryInterface $findAssetItemsForIdentifiersAndQuery;

    private AssetIdentifier $starckIdentifier;

    private AssetIdentifier $cocoIdentifier;

    public function setUp(): void
    {
        parent::setUp();

        $this->findAssetItemsForIdentifiersAndQuery = $this->get('akeneo_assetmanager.infrastructure.persistence.query.find_asset_items_for_identifiers_and_query');
        $this->resetDB();
        $this->loadAssetFamilyAndAssets();
    }

    /**
     * @test
     */
    public function it_returns_empty_collection_if_there_is_no_matching_identifiers()
    {
        $query = AssetQuery::createFromNormalized([
            'channel' => 'ecommerce',
            'locale' => 'en_US',
            'filters' => [
                [
                    'field' => 'asset_family',
                    'operator' => '=',
                    'value' => 'designer'
                ]
            ],
            'page' => 1,
            'size' => 10,
        ]);

        $this->assertEmpty($this->findAssetItemsForIdentifiersAndQuery->find(['michel_sardou', 'bob_ross'], $query));
    }

    /**
     * @test
     */
    public function it_returns_asset_items_for_matching_identifiers_with_same_order()
    {
        $query = AssetQuery::createFromNormalized([
            'channel' => 'ecommerce',
            'locale' => 'en_US',
            'filters' => [
                [
                    'field' => 'asset_family',
                    'operator' => '=',
                    'value' => 'designer'
                ]
            ],
            'page' => 1,
            'size' => 10,
        ]);

        $assetItems = $this->findAssetItemsForIdentifiersAndQuery->find(
            [(string) $this->starckIdentifier, (string) $this->cocoIdentifier],
            $query
        );

        /** @var AssetFamily $assetFamily */
        $assetFamily = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family')
            ->getByIdentifier(AssetFamilyIdentifier::fromString('designer'));
        $labelIdentifier = $assetFamily->getAttributeAsLabelReference()->getIdentifier()->normalize();
        $imageIdentifier = $assetFamily->getAttributeAsMainMediaReference()->getIdentifier()->normalize();
        $attributeAsLabelValueKey = $labelIdentifier . '_fr_FR';

        $starck = new AssetItem();
        $starck->identifier = (string) $this->starckIdentifier;
        $starck->assetFamilyIdentifier = 'designer';
        $starck->code = 'starck';
        $starck->labels = ['fr_FR' => 'Philippe Starck'];
        $starck->values = [
            $attributeAsLabelValueKey => [
                'data' => 'Philippe Starck',
                'channel' => null,
                'locale' => 'fr_FR',
                'attribute' => $labelIdentifier
            ]
        ];
        $starck->completeness = ['complete' => 0, 'required' => 0];
        $starck->image = [];

        $coco = new AssetItem();
        $coco->identifier = (string) $this->cocoIdentifier;
        $coco->assetFamilyIdentifier = 'designer';
        $coco->code = 'coco';
        $coco->labels = ['fr_FR' => 'Coco Chanel'];
        $coco->values = [
            $attributeAsLabelValueKey => [
                'data'      => 'Coco Chanel',
                'channel'   => null,
                'locale'    => 'fr_FR',
                'attribute' => $labelIdentifier,
            ]
        ];
        $coco->completeness = ['complete' => 0, 'required' => 0];
        $coco->image = [];

        $this->assertAssetItem($starck, $assetItems[0]);
        $this->assertAssetItem($coco, $assetItems[1]);
    }

    private function resetDB(): void
    {
        $this->get('akeneoasset_manager.tests.helper.database_helper')->resetDatabase();
    }

    private function loadAssetFamilyAndAssets(): void
    {
        $assetFamilyRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family');
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');
        $assetFamily = AssetFamily::create(
            $assetFamilyIdentifier,
            [
                'fr_FR' => 'Concepteur',
                'en_US' => 'Designer',
            ],
            Image::createEmpty(),
            RuleTemplateCollection::empty()
        );
        $assetFamilyRepository->create($assetFamily);
        $assetFamily = $assetFamilyRepository->getByIdentifier($assetFamilyIdentifier);

        $assetRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset');
        $starkCode = AssetCode::fromString('starck');
        $this->starckIdentifier = $assetRepository->nextIdentifier($assetFamilyIdentifier, $starkCode);
        $labelValue = Value::create(
            $assetFamily->getAttributeAsLabelReference()->getIdentifier(),
            ChannelReference::noReference(),
            LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
            TextData::fromString('Philippe Starck')
        );
        $assetRepository->create(
            Asset::create(
                $this->starckIdentifier,
                $assetFamilyIdentifier,
                $starkCode,
                ValueCollection::fromValues([$labelValue])
            )
        );
        $cocoCode = AssetCode::fromString('coco');
        $this->cocoIdentifier = $assetRepository->nextIdentifier($assetFamilyIdentifier, $cocoCode);
        $labelValue = Value::create(
            $assetFamily->getAttributeAsLabelReference()->getIdentifier(),
            ChannelReference::noReference(),
            LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
            TextData::fromString('Coco Chanel')
        );
        $assetRepository->create(
            Asset::create(
                $this->cocoIdentifier,
                $assetFamilyIdentifier,
                $cocoCode,
                ValueCollection::fromValues([$labelValue])
            )
        );
    }

    private function assertAssetItem(AssetItem $expected, AssetItem $actual): void
    {
        $this->assertEquals($expected->identifier, $actual->identifier, 'Asset identifiers are not equal');
        $this->assertEquals(
            $expected->assetFamilyIdentifier,
            $actual->assetFamilyIdentifier,
            'Asset family identifier are not the same'
        );
        $expectedLabels = $expected->labels;
        $actualLabels = $actual->labels;
        $this->assertEmpty(
            array_merge(
                array_diff($expectedLabels, $actualLabels),
                array_diff($actualLabels, $expectedLabels)
            ),
            'Labels for the asset item are not the same'
        );
        $this->assertEquals($expected->values, $actual->values, 'Values are not the same');
        $this->assertEquals($expected->image, $actual->image, 'Image are not the same');
        $this->assertEquals($expected->completeness, $actual->completeness);
    }
}
