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

namespace Akeneo\AssetManager\Acceptance\Context;

use Akeneo\AssetManager\Application\Asset\SearchAsset\SearchAsset;
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
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifier;
use Akeneo\AssetManager\Domain\Query\Asset\AssetItem;
use Akeneo\AssetManager\Domain\Query\Asset\AssetQuery;
use Akeneo\AssetManager\Domain\Query\Asset\FindIdentifiersForQueryInterface;
use Akeneo\AssetManager\Domain\Query\Asset\SearchAssetResult;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Akeneo\AssetManager\Domain\Repository\AssetRepositoryInterface;
use Behat\Behat\Context\Context;
use PHPUnit\Framework\Assert;

/**
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
final class ListAssetContext implements Context
{
    private ?SearchAssetResult $result = null;

    private AssetRepositoryInterface $assetRepository;

    private AssetFamilyRepositoryInterface $assetFamilyRepository;

    private FindIdentifiersForQueryInterface $findIdentifiersForQuery;

    private SearchAsset $searchAsset;

    public function __construct(
        AssetRepositoryInterface $assetRepository,
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        FindIdentifiersForQueryInterface $findIdentifiersForQuery,
        SearchAsset $searchAsset
    ) {
        $this->assetRepository = $assetRepository;
        $this->assetFamilyRepository = $assetFamilyRepository;
        $this->findIdentifiersForQuery = $findIdentifiersForQuery;
        $this->searchAsset = $searchAsset;
    }

    /**
     * @Given /^a list of assets$/
     */
    public function aListOfAssets()
    {
        $this->loadAssetFamily();
        $this->loadAsset();
    }

    /**
     * @When the user search for :searchInput
     */
    public function theUserSearchFor($searchInput)
    {
        $query = AssetQuery::createFromNormalized([
            'locale' => 'en_US',
            'channel' => 'ecommerce',
            'size' => 20,
            'page' => 0,
            'filters' => [
                [
                    'field' => 'full_text',
                    'operator' => '=',
                    'value' => $searchInput,
                    'context' => []
                ],
                [
                    'field' => 'asset_family',
                    'operator' => '=',
                    'value' => 'designer',
                    'context' => []
                ]
            ]
        ]);

        $this->result = ($this->searchAsset)($query);
    }

    /**
     * @When /^the user filters assets by "([^"]+)" with operator "([^"]+)" and value "([^"]*)"$/
     */
    public function theUserFiltersAssetsByWithOperatorAndValue($filter, $operator, $value)
    {
        $query = AssetQuery::createFromNormalized([
            'locale' => 'en_US',
            'channel' => 'ecommerce',
            'size' => 20,
            'page' => 0,
            'filters' => [
                [
                    'field' => $filter,
                    'operator' => $operator,
                    'value' => $value,
                    'context' => []
                ],
                [
                    'field' => 'asset_family',
                    'operator' => '=',
                    'value' => 'designer',
                    'context' => []
                ]
            ]
        ]);

        $this->result = ($this->searchAsset)($query);
    }

    /**
     * @Then the search result should be :assetCodes
     */
    public function theSearchResultShouldBe(string $expectedAssetCodes)
    {
        $expectedAssetCodes = explode(',', $expectedAssetCodes);
        $resultCodes = array_map(
            fn(AssetItem $assetItem): string => $assetItem->code,
            $this->result->items
        );

        array_map(function (string $expectedAssetCode) use ($resultCodes) {
            Assert::assertContains($expectedAssetCode, $resultCodes);
        }, $expectedAssetCodes);

        Assert::assertCount(count($expectedAssetCodes), $resultCodes, 'More results found than expected');
    }

    /**
     * @Then /^there should be no result on a total of (\d+) assets$/
     */
    public function thereShouldBeNoResult(int $expectedTotalOfAssets)
    {
        Assert::assertEquals(0, $this->result->matchesCount);
        Assert::assertEmpty($this->result->items);
        Assert::assertEquals($expectedTotalOfAssets, $this->result->totalCount);
    }

    /**
     * @When the user list the assets
     */
    public function theUserListTheAssets()
    {
        $query = AssetQuery::createFromNormalized([
            'locale' => 'en_US',
            'channel' => 'ecommerce',
            'size' => 20,
            'page' => 0,
            'filters' => [
                [
                    'field' => 'asset_family',
                    'operator' => '=',
                    'value' => 'designer',
                    'context' => []
                ]
            ]
        ]);

        $this->result = ($this->searchAsset)($query);
    }

    private function loadAsset(): void
    {
        $assetFamilyIdentifierDesigner = AssetFamilyIdentifier::fromString('designer');
        $assetFamilyDesigner = $this->assetFamilyRepository->getByIdentifier($assetFamilyIdentifierDesigner);
        $attributeAsLabelDesigner = $assetFamilyDesigner->getAttributeAsLabelReference();

        $assetFamilyIdentifierAtmosphere = AssetFamilyIdentifier::fromString('atmosphere');
        $assetFamilyAtmosphere = $this->assetFamilyRepository->getByIdentifier($assetFamilyIdentifierAtmosphere);
        $attributeAsLabelAtmosphere = $assetFamilyAtmosphere->getAttributeAsLabelReference();

        // STARCK
        $assetCode = AssetCode::fromString('starck');
        $identifier = AssetIdentifier::fromString('designer_starck_29aea250-bc94-49b2-8259-bbc116410eb2');

        $labelValue = Value::create(
            $attributeAsLabelDesigner->getIdentifier(),
            ChannelReference::noReference(),
            LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
            TextData::fromString(ucfirst((string) $assetCode))
        );

        $assetStarck = Asset::create(
            $identifier,
            $assetFamilyIdentifierDesigner,
            $assetCode,
            ValueCollection::fromValues([$labelValue])
        );
        $this->assetRepository->create($assetStarck);

        // COCO
        $assetCode = AssetCode::fromString('coco');
        $identifier = AssetIdentifier::fromString('designer_coco_34aee120-fa95-4ff2-8439-bea116120e34');

        $labelValue = Value::create(
            $attributeAsLabelDesigner->getIdentifier(),
            ChannelReference::noReference(),
            LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
            TextData::fromString(ucfirst((string) $assetCode))
        );

        $assetCoco = Asset::create(
            $identifier,
            $assetFamilyIdentifierDesigner,
            $assetCode,
            ValueCollection::fromValues([$labelValue])
        );
        $this->assetRepository->create($assetCoco);

        // DYSON
        $assetCode = AssetCode::fromString('dyson');
        $identifier = AssetIdentifier::fromString('designer_dyson_01afdc3e-3ecf-4a86-85ef-e81b2d6e95fd');

        $labelValue = Value::create(
            $attributeAsLabelDesigner->getIdentifier(),
            ChannelReference::noReference(),
            LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
            TextData::fromString(ucfirst((string) $assetCode))
        );

        $assetDyson = Asset::create(
            $identifier,
            $assetFamilyIdentifierDesigner,
            $assetCode,
            ValueCollection::fromValues([$labelValue])
        );
        $this->assetRepository->create($assetDyson);

        // ABSORB_ATMOSPHERE_1
        $assetCode = AssetCode::fromString('absorb_atmosphere_1');
        $identifier = AssetIdentifier::fromString('atmosphere_absorb_atmosphere_1_01afdc3e-3ecf-4a86-85ef-e81b2d6e95fd');

        $labelValue = Value::create(
            $attributeAsLabelAtmosphere->getIdentifier(),
            ChannelReference::noReference(),
            LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
            TextData::fromString(ucfirst((string) $assetCode))
        );

        $assetDyson = Asset::create(
            $identifier,
            $assetFamilyIdentifierAtmosphere,
            $assetCode,
            ValueCollection::fromValues([$labelValue])
        );
        $this->assetRepository->create($assetDyson);
    }

    private function loadAssetFamily(): void
    {
        // DESIGNER
        $assetFamily = AssetFamily::create(
            AssetFamilyIdentifier::fromString('designer'),
            [
                'fr_FR' => 'Concepteur',
                'en_US' => 'Designer',
            ],
            Image::createEmpty(),
            RuleTemplateCollection::empty()
        );
        $this->assetFamilyRepository->create($assetFamily);

        // ATMOSPHERE
        $assetFamily = AssetFamily::create(
            AssetFamilyIdentifier::fromString('atmosphere'),
            [
                'fr_FR' => 'Atmosphère',
                'en_US' => 'Atmosphere',
            ],
            Image::createEmpty(),
            RuleTemplateCollection::empty()
        );
        $this->assetFamilyRepository->create($assetFamily);
    }
}
