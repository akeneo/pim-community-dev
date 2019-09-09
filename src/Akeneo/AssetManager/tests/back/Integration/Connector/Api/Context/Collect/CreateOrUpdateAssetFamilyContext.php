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

namespace Akeneo\AssetManager\Integration\Connector\Api\Context\Collect;

use Akeneo\AssetManager\Common\Fake\InMemoryChannelExists;
use Akeneo\AssetManager\Common\Fake\InMemoryFindActivatedLocalesByIdentifiers;
use Akeneo\AssetManager\Common\Fake\InMemoryFindActivatedLocalesPerChannels;
use Akeneo\AssetManager\Common\Fake\InMemoryGetAttributeIdentifier;
use Akeneo\AssetManager\Common\Helper\OauthAuthenticatedClientFactory;
use Akeneo\AssetManager\Common\Helper\WebClientHelper;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AttributeAsImageReference;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AttributeAsLabelReference;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplateCollection;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\ChannelIdentifier;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifier;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Behat\Behat\Context\Context;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

class CreateOrUpdateAssetFamilyContext implements Context
{
    private const REQUEST_CONTRACT_DIR = 'AssetFamily/Connector/Collect/';

    /** @var OauthAuthenticatedClientFactory */
    private $clientFactory;

    /** @var WebClientHelper */
    private $webClientHelper;

    /** @var AssetFamilyRepositoryInterface */
    private $assetFamilyRepository;

    /** @var null|Response */
    private $pimResponse;

    /** @var null|string */
    private $requestContract;

    /** @var InMemoryChannelExists */
    private $channelExists;

    /** @var InMemoryFindActivatedLocalesByIdentifiers */
    private $activatedLocales;

    /** @var InMemoryFindActivatedLocalesPerChannels */
    private $activatedLocalesPerChannels;

    /** @var InMemoryGetAttributeIdentifier */
    private $getAttributeIdentifier;

    public function __construct(
        OauthAuthenticatedClientFactory $clientFactory,
        WebClientHelper $webClientHelper,
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        InMemoryChannelExists $channelExists,
        InMemoryFindActivatedLocalesByIdentifiers $activatedLocales,
        InMemoryFindActivatedLocalesPerChannels $activatedLocalesPerChannels,
        InMemoryGetAttributeIdentifier $getAttributeIdentifier
    ) {
        $this->clientFactory = $clientFactory;
        $this->webClientHelper = $webClientHelper;
        $this->assetFamilyRepository = $assetFamilyRepository;
        $this->channelExists = $channelExists;
        $this->activatedLocales = $activatedLocales;
        $this->activatedLocalesPerChannels = $activatedLocalesPerChannels;
        $this->getAttributeIdentifier = $getAttributeIdentifier;
    }

    /**
     * @Given the Frontview asset family existing in the ERP but not in the PIM
     */
    public function theFrontviewAssetFamilyExistingInTheErpButNotInThePim()
    {
        $this->requestContract = 'successful_frontview_asset_family_creation.json';

        $this->channelExists->save(ChannelIdentifier::fromCode('ecommerce'));
        $this->activatedLocales->save(LocaleIdentifier::fromCode('en_US'));
        $this->activatedLocales->save(LocaleIdentifier::fromCode('fr_FR'));
    }

    /**
     * @When the connector collects the properties of the Brand asset family from the ERP to synchronize it with the PIM
     */
    public function theConnectorCollectsThePropertiesOfTheBrandAssetFamilyFromTheErpToSynchronizeItWithThePim()
    {
        Assert::assertNotNull($this->requestContract, 'The request contract must be defined first.');

        $client = $this->clientFactory->logIn('julia');
        $this->pimResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR . $this->requestContract
        );
    }

    /**
     * @Then the asset family is created with its properties in the PIM with the information from the ERP
     */
    public function theAssetFamilyIsCreated()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->pimResponse,
            self::REQUEST_CONTRACT_DIR . 'successful_frontview_asset_family_creation.json'
        );

        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('frontview');
        $labelIdentifier = $this->getAttributeIdentifier->withAssetFamilyAndCode(
            $assetFamilyIdentifier,
            AttributeCode::fromString('label')
        );
        $mainImageIdentifier = $this->getAttributeIdentifier->withAssetFamilyAndCode(
            $assetFamilyIdentifier,
            AttributeCode::fromString('image')
        );
        $ruleTemplate = $this->getExpectedRuleTemplate();

        $frontview = $this->assetFamilyRepository->getByIdentifier(AssetFamilyIdentifier::fromString('frontview'));
        $expectedFrontview = AssetFamily::createWithAttributes(
            $assetFamilyIdentifier,
            [
                'en_US' => 'Frontview english label',
                'fr_FR' => 'Frontview french label',
            ],
            Image::createEmpty(),
            AttributeAsLabelReference::fromAttributeIdentifier($labelIdentifier),
            AttributeAsImageReference::fromAttributeIdentifier($mainImageIdentifier),
            RuleTemplateCollection::createFromProductLinkRules([$ruleTemplate])
        );

        Assert::assertEquals($frontview, $expectedFrontview);
    }

    /**
     * @Given the Brand asset family existing in the ERP and the PIM with different properties
     */
    public function theBrandAssetFamilyExistingInTheErpAndInThePimWithDifferentProperties()
    {
        $this->requestContract = 'successful_brand_asset_family_update.json';

        $this->channelExists->save(ChannelIdentifier::fromCode('ecommerce'));
        $this->activatedLocales->save(LocaleIdentifier::fromCode('en_US'));
        $this->activatedLocales->save(LocaleIdentifier::fromCode('fr_FR'));

        $assetFamily = AssetFamily::create(
            AssetFamilyIdentifier::fromString('brand'),
            [
                'en_US' => 'It is an english label'
            ],
            Image::createEmpty(),
            RuleTemplateCollection::empty()
        );

        $this->assetFamilyRepository->create($assetFamily);
    }

    /**
     * @When the connector collects the Brand asset family from the ERP to synchronize it with the PIM
     */
    public function theConnectorCollectsTheBrandAssetFamilyFromTheErpToSynchronizeItWithThePim()
    {
        Assert::assertNotNull($this->requestContract, 'The request contract must be defined first.');

        $client = $this->clientFactory->logIn('julia');
        $this->pimResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR . $this->requestContract
        );
    }

    /**
     * @Then the properties of the asset family are correctly synchronized in the PIM with the information from the ERP
     */
    public function thePropertiesOfTheAssetFamilyAreCorrectlySynchornizedInThePimWithTheInformationFromTheErp()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->pimResponse,
            self::REQUEST_CONTRACT_DIR . 'successful_brand_asset_family_update.json'
        );

        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('brand');
        $labelIdentifier = $this->getAttributeIdentifier->withAssetFamilyAndCode(
            $assetFamilyIdentifier,
            AttributeCode::fromString('label')
        );
        $mainImageIdentifier = $this->getAttributeIdentifier->withAssetFamilyAndCode(
            $assetFamilyIdentifier,
            AttributeCode::fromString('image')
        );
        $ruleTemplate = $this->getExpectedRuleTemplate();

        $brand = $this->assetFamilyRepository->getByIdentifier(AssetFamilyIdentifier::fromString('brand'));
        $expectedBrand = AssetFamily::createWithAttributes(
            $assetFamilyIdentifier,
            [
                'en_US' => 'Brand english label',
                'fr_FR' => 'Brand french label',
            ],
            Image::createEmpty(),
            AttributeAsLabelReference::fromAttributeIdentifier($labelIdentifier),
            AttributeAsImageReference::fromAttributeIdentifier($mainImageIdentifier),
            RuleTemplateCollection::createFromProductLinkRules([$ruleTemplate])
        );

        Assert::assertEquals($brand, $expectedBrand);
    }

    /**
     * @Given some asset families
     */
    public function someAssetFamilies()
    {
        $this->channelExists->save(ChannelIdentifier::fromCode('ecommerce'));
        $this->activatedLocales->save(LocaleIdentifier::fromCode('en_US'));
        $this->activatedLocales->save(LocaleIdentifier::fromCode('fr_FR'));
        $this->activatedLocales->save(LocaleIdentifier::fromCode('de_DE'));

        $assetFamily = AssetFamily::create(
            AssetFamilyIdentifier::fromString('brand'),
            [
                'en_US' => 'It is an english label'
            ],
            Image::createEmpty(),
            RuleTemplateCollection::empty()
        );

        $this->assetFamilyRepository->create($assetFamily);
    }

    /**
     * @When the connector collects an asset family that has an invalid format
     */
    public function collectAAssetFamilyWithAnInvalidFormat()
    {
        $client = $this->clientFactory->logIn('julia');
        $this->pimResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR . 'unprocessable_brand_asset_family_for_invalid_format.json'
        );
    }

    /**
     * @Then the PIM notifies the connector about an error indicating that the asset family has an invalid format
     */
    public function thePimNotifiesTheConnectorAboutAnErrorIndicatingThatTheAssetFamilyHasAnInvalidFormat()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->pimResponse,
            self::REQUEST_CONTRACT_DIR . 'unprocessable_brand_asset_family_for_invalid_format.json'
        );
    }

    /**
     * @When the connector collects an asset family whose data does not comply with the business rules
     */
    public function theConnectorCollectsAAssetFamilyWhoseDataDoesNotComplyWithTheBusinessRules()
    {
        $client = $this->clientFactory->logIn('julia');
        $this->pimResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR . 'unprocessable_brand_asset_family_for_invalid_data.json'
        );
    }

    /**
     * @Then the PIM notifies the connector about an error indicating that the asset family has data that does not comply with the business rules
     */
    public function thePimNotifiesTheConnectorAboutAnErrorIndicatingThatTheAssetFamilyHasDataThatDoesNotComplyWithTheBusinessRules()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->pimResponse,
            self::REQUEST_CONTRACT_DIR . 'unprocessable_brand_asset_family_for_invalid_data.json'
        );
    }

    /**
     * @return array
     */
    private function getExpectedRuleTemplate(): array
    {
        return [
            'product_selections' => [
                [
                    'field'    => 'sku',
                    'operator' => 'equals',
                    'value'    => '123134124123'
                ],
                [
                    'field'    => 'enabled',
                    'operator' => '=',
                    'value'    => true
                ],
                [
                    'field'    => 'categories',
                    'operator' => 'IN CHILDREN',
                    'value'    => ['shoes', 'tshirts']
                ]
            ],
            'assign_assets_to'    => [
                [
                    'mode'  => 'add',
                    'attribute' => 'product_asset_collection',
                ]
            ]
        ];
    }
}
