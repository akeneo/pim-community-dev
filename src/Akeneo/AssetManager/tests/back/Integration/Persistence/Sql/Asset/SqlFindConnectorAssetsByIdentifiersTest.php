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
use Akeneo\AssetManager\Domain\Model\ChannelIdentifier;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifier;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifierCollection;
use Akeneo\AssetManager\Domain\Query\Asset\AssetQuery;
use Akeneo\AssetManager\Domain\Query\Asset\Connector\ConnectorAsset;
use Akeneo\AssetManager\Domain\Query\Asset\Connector\FindConnectorAssetsByIdentifiersInterface;
use Akeneo\AssetManager\Domain\Repository\AssetRepositoryInterface;
use Akeneo\AssetManager\Integration\SqlIntegrationTestCase;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;

class SqlFindConnectorAssetsByIdentifiersTest extends SqlIntegrationTestCase
{
    /** @var AssetRepositoryInterface */
    private $repository;

    /** @var FindConnectorAssetsByIdentifiersInterface */
    private $findConnectorAssetsQuery;

    /** @var SaverInterface */
    private $fileInfoSaver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset');
        $this->findConnectorAssetsQuery = $this->get('akeneo_assetmanager.infrastructure.persistence.query.find_connector_assets_by_identifiers');
        $this->fileInfoSaver = $this->get('akeneo_file_storage.saver.file');

        $this->resetDB();
        $this->loadAssetFamilyWithAttributes();
    }

    /**
     * @test
     */
    public function it_finds_assets_from_a_list_of_identifiers()
    {
        $this->loadAssets(['starck', 'dyson', 'newson']);
        $this->loadAssets(['unexpected_asset']);

        $assetQuery = AssetQuery::createPaginatedQueryUsingSearchAfter(
            AssetFamilyIdentifier::fromString('designer'),
            ChannelReference::noReference(),
            LocaleIdentifierCollection::empty(),
            100,
            null,
            []
        );
        $identifiers = ['designer_dyson_fingerprint', 'designer_newson_fingerprint', 'designer_starck_fingerprint'];

        $expectedConnectorAssets = [];
        foreach (['dyson', 'newson', 'starck'] as $code) {
            $expectedConnectorAssets[] = new ConnectorAsset(
                AssetCode::fromString($code),
                [
                    'name'  => [
                        [
                            'locale'  => 'en_US',
                            'channel' => 'ecommerce',
                            'data'    => sprintf('Name: %s', $code),
                        ],
                        [
                            'locale'  => 'en_US',
                            'channel' => 'print',
                            'data'    => sprintf('Name: %s for print channel', $code),
                        ],
                        [
                            'locale'  => 'fr_FR',
                            'channel' => 'ecommerce',
                            'data'    => sprintf('Nom: %s', $code),
                        ]
                    ]
                ]
            );
        }

        $connectorAssetsFound = $this->findConnectorAssetsQuery->find($identifiers, $assetQuery);

        $this->assertSameConnectorAssets($expectedConnectorAssets, $connectorAssetsFound);
    }

    /**
     * @test
     */
    public function it_finds_assets_from_a_list_of_identifiers_with_values_filtered_by_channel()
    {
        $this->loadAssets(['starck', 'dyson', 'newson']);
        $this->loadAssets(['unexpected_asset']);

        $assetQuery = AssetQuery::createPaginatedQueryUsingSearchAfter(
            AssetFamilyIdentifier::fromString('designer'),
            ChannelReference::createFromNormalized('ecommerce'),
            LocaleIdentifierCollection::empty(),
            100,
            null,
            []
        );
        $identifiers = ['designer_dyson_fingerprint', 'designer_newson_fingerprint', 'designer_starck_fingerprint'];

        $expectedConnectorAssets = [];
        foreach (['dyson', 'newson', 'starck'] as $code) {
            $expectedConnectorAssets[] = new ConnectorAsset(
                AssetCode::fromString($code),
                [
                    'name'  => [
                        [
                            'locale'  => 'en_US',
                            'channel' => 'ecommerce',
                            'data'    => sprintf('Name: %s', $code),
                        ],
                        [
                            'locale'  => 'fr_FR',
                            'channel' => 'ecommerce',
                            'data'    => sprintf('Nom: %s', $code),
                        ]
                    ]
                ]
            );
        }

        $connectorAssetsFound = $this->findConnectorAssetsQuery->find($identifiers, $assetQuery);

        $this->assertSameConnectorAssets($expectedConnectorAssets, $connectorAssetsFound);
    }

    /**
     * @test
     */
    public function it_finds_assets_from_a_list_of_identifiers_with_values_filtered_by_locales()
    {
        $this->loadAssets(['starck', 'dyson', 'newson']);
        $this->loadAssets(['unexpected_asset']);

        $assetQuery = AssetQuery::createPaginatedQueryUsingSearchAfter(
            AssetFamilyIdentifier::fromString('designer'),
            ChannelReference::createFromNormalized('ecommerce'),
            LocaleIdentifierCollection::fromNormalized(['fr_FR']),
            100,
            null,
            []
        );
        $identifiers = ['designer_dyson_fingerprint', 'designer_newson_fingerprint', 'designer_starck_fingerprint'];

        $expectedConnectorAssets = [];
        foreach (['dyson', 'newson', 'starck'] as $code) {
            $expectedConnectorAssets[] = new ConnectorAsset(
                AssetCode::fromString($code),
                [
                    'name'  => [
                        [
                            'locale'  => 'fr_FR',
                            'channel' => 'ecommerce',
                            'data'    => sprintf('Nom: %s', $code),
                        ]
                    ]
                ]
            );
        }

        $connectorAssetsFound = $this->findConnectorAssetsQuery->find($identifiers, $assetQuery);

        $this->assertSameConnectorAssets($expectedConnectorAssets, $connectorAssetsFound);
    }

    /**
     * @test
     */
    public function it_returns_an_empty_array_if_no_assets_found()
    {
        $this->loadAssets(['starck', 'dyson']);

        $assetQuery = AssetQuery::createPaginatedQueryUsingSearchAfter(
            AssetFamilyIdentifier::fromString('designer'),
            ChannelReference::noReference(),
            LocaleIdentifierCollection::empty(),
            100,
            null,
            []
        );

        $assetsFound = $this->findConnectorAssetsQuery->find(['foo', 'bar'], $assetQuery);
        $this->assertSame([], $assetsFound);
    }

    /**
     * @param ConnectorAsset[] $expectedConnectorAssets
     * @param ConnectorAsset[] $connectorAssetsFound
     */
    private function assertSameConnectorAssets(array $expectedConnectorAssets, array $connectorAssetsFound): void
    {
        $this->assertCount(count($expectedConnectorAssets), $connectorAssetsFound);

        foreach ($expectedConnectorAssets as $index => $connectorAsset) {
            $this->assertSameConnectorAsset($connectorAsset, $connectorAssetsFound[$index]);
        }
    }

    private function assertSameConnectorAsset(ConnectorAsset $expectedAsset, ConnectorAsset $currentAsset): void
    {
        $expectedAsset = $expectedAsset->normalize();
        $expectedAsset['values'] = $this->sortAssetValues($expectedAsset['values']);

        $currentAsset = $currentAsset->normalize();
        $currentAsset['values'] = $this->sortAssetValues($currentAsset['values']);

        $this->assertSame($expectedAsset, $currentAsset);
    }

    private function sortAssetValues(array $assetValues): array
    {
        ksort($assetValues);

        foreach ($assetValues as $attributeCode => $assetValue) {
            usort($assetValue, function ($firstValue, $secondValue) {
                $firstData = is_array($firstValue['data']) ? implode(',', sort($firstValue['data'])) : $firstValue['data'];
                $secondData = is_array($secondValue['data']) ? implode(',', sort($secondValue['data'])) : $secondValue['data'];

                return strcasecmp($firstData, $secondData);
            });

            $assetValues[$attributeCode] = $assetValue;
        }

        return $assetValues;
    }

    private function resetDB(): void
    {
        $this->get('akeneoasset_manager.tests.helper.database_helper')->resetDatabase();
    }

    /**
     * @param string[] $codes
     */
    private function loadAssets(array $codes): void
    {
        foreach ($codes as $code) {
            $asset = Asset::create(
                AssetIdentifier::fromString(sprintf('designer_%s_fingerprint', $code)),
                AssetFamilyIdentifier::fromString('designer'),
                AssetCode::fromString($code),
                ValueCollection::fromValues([
                    Value::create(
                        AttributeIdentifier::fromString('name_designer_fingerprint'),
                        ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('ecommerce')),
                        LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                        TextData::fromString(sprintf('Name: %s', $code))
                    ),
                    Value::create(
                        AttributeIdentifier::fromString('name_designer_fingerprint'),
                        ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('print')),
                        LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                        TextData::fromString(sprintf('Name: %s for print channel', $code))
                    ),
                    Value::create(
                        AttributeIdentifier::fromString('name_designer_fingerprint'),
                        ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('ecommerce')),
                        LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
                        TextData::fromString(sprintf('Nom: %s', $code))
                    )
                ])
            );

            $assets[] = $asset;
            $this->repository->create($asset);
        }
    }

    private function loadAssetFamilyWithAttributes(): void
    {
        $repository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family');
        $assetFamily = AssetFamily::create(
            AssetFamilyIdentifier::fromString('designer'),
            [],
            Image::createEmpty(),
            RuleTemplateCollection::empty()
        );
        $repository->create($assetFamily);

        $name = TextAttribute::createText(
            AttributeIdentifier::create('designer', 'name', 'fingerprint'),
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['en_US' => 'Name']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(155),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );

        $image = MediaFileAttribute::create(
            AttributeIdentifier::create('designer', 'main_image', 'fingerprint'),
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString('main_image'),
            LabelCollection::fromArray(['en_US' => 'Image']),
            AttributeOrder::fromInteger(3),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxFileSize::fromString('250.2'),
            AttributeAllowedExtensions::fromList(['png']),
            MediaType::fromString(MediaType::IMAGE)
        );

        $attributesRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.attribute');
        $attributesRepository->create($name);
        $attributesRepository->create($image);
    }
}
