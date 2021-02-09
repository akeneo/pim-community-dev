<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Integration\Search\Elasticsearch\Asset;

use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ChannelReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\FileData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\LocaleReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\TextData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\Value;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ValueCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplateCollection;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeAllowedExtensions;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsReadOnly;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeMaxFileSize;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFile\MediaType;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFileAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\TextAttribute;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifier;
use Akeneo\AssetManager\Domain\Repository\AssetIndexerInterface;
use Akeneo\AssetManager\Infrastructure\Search\Elasticsearch\Asset\IndexAssetEventAggregator;
use Akeneo\AssetManager\Integration\SearchIntegrationTestCase;
use PHPUnit\Framework\Assert;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Testing the search usecases for the asset grid for information in the code of the asset.
 *
 * @see       https://akeneo.atlassian.net/wiki/spaces/AKN/pages/572424236/Search+an+entity+asset
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class AssetIndexerTest extends SearchIntegrationTestCase
{
    private const STARK_ASSET_IDENTIFIER = 'stark_designer_fingerprint';
    private const COCO_ASSET_IDENTIFIER = 'coco_designer_fingerprint';

    /** @var AssetIndexerInterface */
    private $assetIndexer;

    /** * @var IndexAssetEventAggregator */
    private $indexAssetsEventAggregator;

    public function setUp(): void
    {
        parent::setUp();

        $this->assetIndexer = $this->get('akeneo_assetmanager.infrastructure.search.elasticsearch.asset_indexer');
        $this->indexAssetsEventAggregator = $this->get('akeneo_assetmanager.infrastructure.search.elasticsearch.asset.index_asset_event_aggregator');
        $this->loadFixtures();
    }

    /**
     * @test
     */
    public function it_indexes_one_asset()
    {
        $this->searchAssetIndexHelper->resetIndex();
        $this->searchAssetIndexHelper->assertAssetDoesNotExists('designer', 'stark');

        $this->assetIndexer->index(AssetIdentifier::fromString(self::STARK_ASSET_IDENTIFIER));
        $this->indexAssetsEventAggregator->flushEvents();

        $this->searchAssetIndexHelper->assertAssetExists('designer', 'stark');
    }

    /**
     * @test
     */
    public function it_indexes_multiple_assets_by_identifiers()
    {
        $this->searchAssetIndexHelper->resetIndex();
        $this->searchAssetIndexHelper->assertAssetDoesNotExists('designer', 'stark');
        $this->searchAssetIndexHelper->assertAssetDoesNotExists('designer', 'coco');

        $this->assetIndexer->indexByAssetIdentifiers(
            [
                AssetIdentifier::fromString(self::STARK_ASSET_IDENTIFIER),
                AssetIdentifier::fromString(self::COCO_ASSET_IDENTIFIER),
            ]
        );

        $this->searchAssetIndexHelper->assertAssetExists('designer', 'stark');
        $this->searchAssetIndexHelper->assertAssetExists('designer', 'coco');
    }

    /**
     * @test
     */
    public function it_indexes_by_asset_family()
    {
        $this->searchAssetIndexHelper->resetIndex();
        $this->searchAssetIndexHelper->assertAssetDoesNotExists('designer', 'stark');
        $this->searchAssetIndexHelper->assertAssetDoesNotExists('designer', 'coco');

        $this->assetIndexer->indexByAssetFamily(AssetFamilyIdentifier::fromString('designer'));

        $this->searchAssetIndexHelper->assertAssetExists('designer', 'stark');
        $this->searchAssetIndexHelper->assertAssetExists('designer', 'coco');
    }

    /**
     * @test
     */
    public function it_deletes_one_asset()
    {
        $this->assetIndexer->removeAssetByAssetFamilyIdentifierAndCode('designer', 'stark');

        $this->searchAssetIndexHelper->assertAssetDoesNotExists('designer', 'stark');
        $this->searchAssetIndexHelper->assertAssetExists('designer', 'coco');
        Assert::assertCount(1, $this->searchAssetIndexHelper->findAssetsByAssetFamily('designer'));
        Assert::assertCount(1, $this->searchAssetIndexHelper->findAssetsByAssetFamily('another_asset_family'));
    }

    /**
     * @test
     */
    public function it_deletes_multiple_assets_by_asset_family_and_codes()
    {
        $this->assetIndexer->removeByAssetFamilyIdentifierAndCodes('designer', ['stark', 'coco']);

        $this->searchAssetIndexHelper->assertAssetDoesNotExists('designer', 'stark');
        $this->searchAssetIndexHelper->assertAssetDoesNotExists('designer', 'coco');
        Assert::assertCount(0, $this->searchAssetIndexHelper->findAssetsByAssetFamily('designer'));
        Assert::assertCount(1, $this->searchAssetIndexHelper->findAssetsByAssetFamily('another_asset_family'));
    }

    /**
     * @test
     */
    public function it_refreshes_the_index()
    {
        $isExceptionThrown = false;
        try {
            $this->assetIndexer->refresh();
        } catch (\Exception $e) {
            $isExceptionThrown = true;
        }
        Assert::assertFalse($isExceptionThrown, 'An unexpected exception has been thrown');
    }

    private function loadFixtures()
    {
        $this->searchAssetIndexHelper->resetIndex();
        $this->get('akeneoasset_manager.tests.helper.database_helper')->resetDatabase();
        $this->loadAssetFamilies();
        $this->loadAttributes();
        $this->loadAssets();
        $this->searchAssetIndexHelper->refreshIndex();
    }

    private function loadAssetFamilies(): void
    {
        $assetFamilyRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family');
        $assetFamilyRepository->create(
            AssetFamily::create(
                AssetFamilyIdentifier::fromString('designer'),
                [],
                Image::createEmpty(),
                RuleTemplateCollection::empty()
            )
        );

        $assetFamilyRepository->create(
            AssetFamily::create(
                AssetFamilyIdentifier::fromString('another_asset_family'),
                [],
                Image::createEmpty(),
                RuleTemplateCollection::empty()
            )
        );
    }

    private function loadAttributes(): void
    {
        $attributeRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.attribute');
        $attributeRepository->create(
            TextAttribute::createText(
                AttributeIdentifier::create('designer', 'name', 'fingerprint'),
                AssetFamilyIdentifier::fromString('designer'),
                AttributeCode::fromString('name'),
                LabelCollection::fromArray(['fr_FR' => 'Nom']),
                AttributeOrder::fromInteger(2),
                AttributeIsRequired::fromBoolean(false),
                AttributeIsReadOnly::fromBoolean(false),
                AttributeValuePerChannel::fromBoolean(false),
                AttributeValuePerLocale::fromBoolean(false),
                AttributeMaxLength::fromInteger(25),
                AttributeValidationRule::none(),
                AttributeRegularExpression::createEmpty()
            )
        );

        $attributeRepository->create(
            MediaFileAttribute::create(
                AttributeIdentifier::create('designer', 'image', 'fingerprint'),
                AssetFamilyIdentifier::fromString('designer'),
                AttributeCode::fromString('portrait'),
                LabelCollection::fromArray(['fr_FR' => 'Image autobiographique', 'en_US' => 'Portrait']),
                AttributeOrder::fromInteger(3),
                AttributeIsRequired::fromBoolean(true),
                AttributeIsReadOnly::fromBoolean(false),
                AttributeValuePerChannel::fromBoolean(false),
                AttributeValuePerLocale::fromBoolean(false),
                AttributeMaxFileSize::fromString('200.10'),
                AttributeAllowedExtensions::fromList(['gif']),
                MediaType::fromString(MediaType::IMAGE)
            )
        );

        $attributeRepository->create(
            TextAttribute::createText(
                AttributeIdentifier::create('another_asset_family', 'name', 'fingerprint'), AssetFamilyIdentifier::fromString('another_asset_family'),
                AttributeCode::fromString('name'),
                LabelCollection::fromArray(['fr_FR' => 'Nom']),
                AttributeOrder::fromInteger(2),
                AttributeIsRequired::fromBoolean(false),
                AttributeIsReadOnly::fromBoolean(false),
                AttributeValuePerChannel::fromBoolean(false),
                AttributeValuePerLocale::fromBoolean(false),
                AttributeMaxLength::fromInteger(25),
                AttributeValidationRule::none(),
                AttributeRegularExpression::createEmpty()
            )
        );
    }

    private function loadAssets(): void
    {
        $assetRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset');
        $assetRepository->create(
            Asset::create(
                AssetIdentifier::fromString(self::STARK_ASSET_IDENTIFIER),
                AssetFamilyIdentifier::fromString('designer'),
                AssetCode::fromString('stark'),
                ValueCollection::fromValues([
                    Value::create(
                        AttributeIdentifier::fromString('label_designer_fingerprint'),
                        ChannelReference::noReference(),
                        LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
                        TextData::fromString('Philippe Starck')
                    ),
                    Value::create(
                        AttributeIdentifier::create('designer', 'name', 'fingerprint'),
                        ChannelReference::noReference(),
                        LocaleReference::noReference(),
                        TextData::fromString('Philippe stark')
                    ),
                    Value::create(
                        AttributeIdentifier::create('designer', 'image', 'fingerprint'),
                        ChannelReference::noReference(),
                        LocaleReference::noReference(),
                        FileData::createFromNormalize([
                                'filePath'         => 'f/r/z/a/oihdaozijdoiaaodoaoiaidjoaihd',
                                'originalFilename' => 'file.gif',
                                'size'             => 1024,
                                'mimeType'         => 'image/gif',
                                'extension'        => 'gif',
                                'updatedAt'        => '2019-11-22T15:16:21+0000',
                            ]
                        )
                    ),
                ])
            )
        );

        $assetRepository->create(
            Asset::create(
                AssetIdentifier::fromString(self::COCO_ASSET_IDENTIFIER),
                AssetFamilyIdentifier::fromString('designer'),
                AssetCode::fromString('coco'),
                ValueCollection::fromValues([
                    Value::create(
                        AttributeIdentifier::fromString('label_designer_fingerprint'),
                        ChannelReference::noReference(),
                        LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
                        TextData::fromString('Coco')
                    ),
                    Value::create(
                        AttributeIdentifier::create('designer', 'name', 'fingerprint'),
                        ChannelReference::noReference(),
                        LocaleReference::noReference(),
                        TextData::fromString('Coco Chanel')
                    ),
                    Value::create(
                        AttributeIdentifier::fromString('image'),
                        ChannelReference::noReference(),
                        LocaleReference::noReference(),
                        FileData::createFromNormalize([
                                'filePath'         => 'f/r/z/a/oihdaozijdoiaaodoaoiaidjoaihd',
                                'originalFilename' => 'coco.gif',
                                'size'             => 1024,
                                'mimeType'         => 'image/gif',
                                'extension'        => 'gif',
                                'updatedAt'        => '2019-11-22T15:16:21+0000',
                            ]
                        )
                    ),
                ])
            )
        );

        $assetRepository->create(
            Asset::create(
                AssetIdentifier::fromString('another_asset_another_asset_family'),
                AssetFamilyIdentifier::fromString('another_asset_family'),
                AssetCode::fromString('another_asset'),
                ValueCollection::fromValues([
                    Value::create(
                        AttributeIdentifier::fromString('label_designer_fingerprint'),
                        ChannelReference::noReference(),
                        LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
                        TextData::fromString('Coco')
                    ),
                    Value::create(
                        AttributeIdentifier::create('another_asset_family', 'name', 'fingerprint'),
                        ChannelReference::noReference(),
                        LocaleReference::noReference(),
                        TextData::fromString('Another name')
                    ),
                ])
            )
        );
        $this->flushAssetsToIndexCache();
    }
}
