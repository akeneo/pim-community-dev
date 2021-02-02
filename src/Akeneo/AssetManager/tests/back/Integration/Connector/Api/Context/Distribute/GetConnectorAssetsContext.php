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

namespace Akeneo\AssetManager\Integration\Connector\Api\Context\Distribute;

use Akeneo\AssetManager\Common\Fake\Connector\InMemoryFindConnectorAssetsByIdentifiers;
use Akeneo\AssetManager\Common\Fake\InMemoryAttributeRepository;
use Akeneo\AssetManager\Common\Fake\InMemoryChannelExists;
use Akeneo\AssetManager\Common\Fake\InMemoryDateRepository;
use Akeneo\AssetManager\Common\Fake\InMemoryFindActivatedLocalesByIdentifiers;
use Akeneo\AssetManager\Common\Fake\InMemoryFindActivatedLocalesPerChannels;
use Akeneo\AssetManager\Common\Fake\InMemoryFindAssetIdentifiersForQuery;
use Akeneo\AssetManager\Common\Fake\InMemoryFindRequiredValueKeyCollectionForChannelAndLocales;
use Akeneo\AssetManager\Common\Helper\OauthAuthenticatedClientFactory;
use Akeneo\AssetManager\Common\Helper\WebClientHelper;
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
use Akeneo\AssetManager\Domain\Model\ChannelIdentifier;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifier;
use Akeneo\AssetManager\Domain\Query\Asset\Connector\ConnectorAsset;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use Behat\Behat\Context\Context;
use Behat\Behat\Tester\Exception\PendingException;
use Symfony\Component\HttpFoundation\Response;
use Webmozart\Assert\Assert;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class GetConnectorAssetsContext implements Context
{
    private const REQUEST_CONTRACT_DIR = 'Asset/Connector/Distribute/';

    /** @var OauthAuthenticatedClientFactory */
    private $clientFactory;

    /** @var WebClientHelper */
    private $webClientHelper;

    /** @var InMemoryFindConnectorAssetsByIdentifiers */
    private $findConnectorAssets;

    /** @var AssetFamilyRepositoryInterface */
    private $assetFamilyRepository;

    /** @var InMemoryAttributeRepository */
    private $attributeRepository;

    /** @var array */
    private $assetPages;

    /** @var InMemoryFindAssetIdentifiersForQuery */
    private $findAssetIdentifiersForQuery;

    /** @var null|Response */
    private $unprocessableEntityResponse;

    /** @var null|Response */
    private $updatedSinceWrongFormatResponse;

    /** @var null|Response */
    private $updatedSinceResponse;

    /** @var InMemoryChannelExists */
    private $channelExists;

    /** @var ConnectorAsset[] */
    private $connectorAssetsByAssetIdentifier;

    /** @var InMemoryFindActivatedLocalesByIdentifiers */
    private $findActivatedLocalesByIdentifiers;

    /** @var InMemoryFindRequiredValueKeyCollectionForChannelAndLocales */
    private $findRequiredValueKeyCollectionForChannelAndLocales;

    /** @var null|string */
    private $requestContract;

    /** @var InMemoryFindActivatedLocalesPerChannels */
    private $findActivatedLocalesPerChannels;

    /** @var InMemoryDateRepository */
    private $dateRepository;

    public function __construct(
        OauthAuthenticatedClientFactory $clientFactory,
        WebClientHelper $webClientHelper,
        InMemoryFindAssetIdentifiersForQuery $findAssetIdentifiersForQuery,
        InMemoryFindConnectorAssetsByIdentifiers $findConnectorAssets,
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        InMemoryAttributeRepository $attributeRepository,
        InMemoryChannelExists $channelExists,
        InMemoryFindActivatedLocalesByIdentifiers $findActivatedLocalesByIdentifiers,
        InMemoryFindRequiredValueKeyCollectionForChannelAndLocales $findRequiredValueKeyCollectionForChannelAndLocales,
        InMemoryFindActivatedLocalesPerChannels $findActivatedLocalesPerChannels,
        InMemoryDateRepository $dateRepository
    ) {
        $this->clientFactory = $clientFactory;
        $this->webClientHelper = $webClientHelper;
        $this->findConnectorAssets = $findConnectorAssets;
        $this->assetFamilyRepository = $assetFamilyRepository;
        $this->attributeRepository = $attributeRepository;
        $this->assetPages = [];
        $this->findAssetIdentifiersForQuery = $findAssetIdentifiersForQuery;
        $this->channelExists = $channelExists;
        $this->findActivatedLocalesByIdentifiers = $findActivatedLocalesByIdentifiers;
        $this->findRequiredValueKeyCollectionForChannelAndLocales = $findRequiredValueKeyCollectionForChannelAndLocales;
        $this->findActivatedLocalesPerChannels = $findActivatedLocalesPerChannels;
        $this->dateRepository = $dateRepository;
    }

    /**
     * @Given /^([\d]+) assets for the ([\S]+) asset family$/
     */
    public function theAssetsForTheAssetFamily(int $numberOfAssets, string $assetFamilyIdentifier): void
    {
        $assetFamilyIdentifier = strtolower($assetFamilyIdentifier);

        for ($i = 1; $i <= $numberOfAssets; $i++) {
            $rawAssetCode = sprintf('%s_%d', $assetFamilyIdentifier, $i);
            $assetCode = AssetCode::fromString($rawAssetCode);
            $assetIdentifier = AssetIdentifier::fromString(sprintf('%s_fingerprint', $rawAssetCode));
            $labelCollection = [
                'en_US' => sprintf('%s number %d', ucfirst($assetFamilyIdentifier), $i)
            ];

            $mainImageInfo = (new FileInfo())
                ->setOriginalFilename(sprintf('%s_image.jpg', $rawAssetCode))
                ->setKey(sprintf('test/%s_image.jpg', $rawAssetCode));
            $mainImage = Image::fromFileInfo($mainImageInfo);

            Asset::create(
                $assetIdentifier,
                AssetFamilyIdentifier::fromString($assetFamilyIdentifier),
                $assetCode,
                ValueCollection::fromValues([
                    Value::create(
                        AttributeIdentifier::fromString('label_brand_fingerprint'),
                        ChannelReference::noReference(),
                        LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                        TextData::fromString(sprintf('%s number %d', ucfirst($assetFamilyIdentifier), $i))
                    ),
                    Value::create(
                        AttributeIdentifier::fromString('main_image_brand_fingerprint'),
                        ChannelReference::noReference(),
                        LocaleReference::noReference(),
                        FileData::createFromFileinfo($mainImageInfo, \DateTimeImmutable::createFromFormat(\DateTimeImmutable::ISO8601, '2019-11-22T15:16:21+0000'))
                    ),
                ])
            );

            $connectorAsset = new ConnectorAsset(
                $assetCode,
                [
                    'label' => [
                        [
                            'locale'  => 'en_US',
                            'channel' => null,
                            'value'   => $labelCollection['en_US']
                        ]
                    ],
                    'description' => [
                        [
                            'locale' => 'en_US',
                            'channel' => null,
                            'data' => sprintf('%s example %d', ucfirst($assetFamilyIdentifier), $i)
                        ]
                    ],
                    'country' => [
                        [
                            'locale' => null,
                            'channel' => null,
                            'data' => 'italy'
                        ]
                    ],
                    AssetFamily::DEFAULT_ATTRIBUTE_AS_MAIN_MEDIA_CODE => [
                        [
                            'locale' => null,
                            'channel' => null,
                            'data' => $mainImage->getKey()
                        ]
                    ]
                ]
            );

            $this->findConnectorAssets->save($assetIdentifier, $connectorAsset);
        }

        $assetFamily = AssetFamily::create(
            AssetFamilyIdentifier::fromString($assetFamilyIdentifier),
            [],
            Image::createEmpty(),
            RuleTemplateCollection::empty()
        );
        $this->assetFamilyRepository->create($assetFamily);
    }

    /**
     * @When /^the connector requests all assets of the ([\S]+) asset family$/
     */
    public function theConnectorRequestsAllAssetsOfTheAssetFamily(string $assetFamilyIdentifier): void
    {
        $client = $this->clientFactory->logIn('julia');
        $this->assetPages = [];

        for ($page = 1; $page <= 4; $page++) {
            $this->assetPages[$page] = $this->webClientHelper->requestFromFile(
                $client,
                self::REQUEST_CONTRACT_DIR . sprintf(
                    "successful_%s_assets_page_%d.json",
                    strtolower($assetFamilyIdentifier),
                    $page
                )
            );
        }
    }

    /**
     * @Then /^the PIM returns the [\d]+ assets of the ([\S]+) asset family$/
     */
    public function thePimReturnsTheAssetsOfTheAssetFamily(string $assetFamilyIdentifier): void
    {
        for ($page = 1; $page <= 4; $page++) {
            Assert::keyExists($this->assetPages, $page, sprintf('The page %d has not been loaded', $page));
            $this->webClientHelper->assertJsonFromFile(
                $this->assetPages[$page],
                self::REQUEST_CONTRACT_DIR . sprintf(
                    "successful_%s_assets_page_%d.json",
                    strtolower($assetFamilyIdentifier),
                    $page
                )
            );
        }
    }

    /**
     * @Given 3 assets for the Brand asset family with filled attribute values for the Ecommerce and the Tablet channels
     */
    public function theAssetsForTheBrandAssetFamilyWithFilledAttributesValuesForTwoChannels(): void
    {
        $assetFamilyIdentifier = 'brand';
        $firstChannel = 'ecommerce';
        $secondChannel = 'tablet';

        $this->channelExists->save(ChannelIdentifier::fromCode($firstChannel));
        $this->channelExists->save(ChannelIdentifier::fromCode($secondChannel));

        for ($i = 1; $i <= 3; $i++) {
            $rawAssetCode = sprintf('%s_%d', $assetFamilyIdentifier, $i);
            $assetCode = AssetCode::fromString($rawAssetCode);
            $assetIdentifier = AssetIdentifier::fromString(sprintf('%s_fingerprint', $rawAssetCode));
            $labelCollection = [
                'en_US' => sprintf('%s number %d', ucfirst($assetFamilyIdentifier), $i)
            ];

            $mainImageInfo = (new FileInfo())
                ->setOriginalFilename(sprintf('%s_image.jpg', $rawAssetCode))
                ->setKey(sprintf('test/%s_image.jpg', $rawAssetCode));
            $mainImage = Image::fromFileInfo($mainImageInfo);

            Asset::create(
                $assetIdentifier,
                AssetFamilyIdentifier::fromString($assetFamilyIdentifier),
                $assetCode,
                ValueCollection::fromValues([
                    Value::create(
                        AttributeIdentifier::fromString('label_brand_fingerprint'),
                        ChannelReference::noReference(),
                        LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                        TextData::fromString(sprintf('%s number %d', ucfirst($assetFamilyIdentifier), $i))
                    ),
                    Value::create(
                        AttributeIdentifier::fromString('main_image_brand_fingerprint'),
                        ChannelReference::noReference(),
                        LocaleReference::noReference(),
                        FileData::createFromFileinfo($mainImageInfo, \DateTimeImmutable::createFromFormat(\DateTimeImmutable::ISO8601, '2019-11-22T15:16:21+0000'))
                    ),
                ])
            );

            $connectorAsset = new ConnectorAsset(
                $assetCode,
                [
                    'label' => [
                        [
                            'locale'  => 'en_US',
                            'channel' => null,
                            'value'   => $labelCollection['en_US']
                        ]
                    ],
                    'description' => [
                        [
                            'locale' => 'en_US',
                            'channel' => $firstChannel,
                            'data' => sprintf(
                                'Description for %s number %d and channel %s',
                                ucfirst($assetFamilyIdentifier),
                                $i,
                                $firstChannel
                            )
                        ],
                        [
                            'locale' => 'en_US',
                            'channel' => $secondChannel,
                            'data' => sprintf(
                                'Description for %s number %d and channel %s',
                                ucfirst($assetFamilyIdentifier),
                                $i,
                                $secondChannel
                            )
                        ]
                    ],
                    'country' => [
                        [
                            'locale' => null,
                            'channel' => null,
                            'data' => 'italy'
                        ]
                    ],
                    AssetFamily::DEFAULT_ATTRIBUTE_AS_MAIN_MEDIA_CODE => [
                        [
                            'locale' => null,
                            'channel' => null,
                            'data' => $mainImage->getKey()
                        ]
                    ]
                ]
            );

            $this->findConnectorAssets->save($assetIdentifier, $connectorAsset);
        }

        $assetFamily = AssetFamily::create(
            AssetFamilyIdentifier::fromString($assetFamilyIdentifier),
            [],
            Image::createEmpty(),
            RuleTemplateCollection::empty()
        );
        $this->assetFamilyRepository->create($assetFamily);
    }

    /**
     * @When the connector requests all assets of the Brand asset family with the information of the Ecommerce channel
     */
    public function theConnectorRequestsAllAssetsOfTheBrandAssetFamilyWithTheInformationOfTheEcommerceChannel(): void
    {
        $client = $this->clientFactory->logIn('julia');
        $this->assetPages = [];

        $this->assetPages[1] = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR . 'successful_brand_assets_for_ecommerce_channel.json'
        );
    }

    /**
     * @Then the PIM returns 3 assets of the Brand asset family with only the information of the Ecommerce channel
     */
    public function thePimReturnsAllAssetsOfTheBrandAssetFamilyWithOnlyAttributeValuesOfTheEcommerceChannel(): void
    {
        Assert::keyExists($this->assetPages, 1, 'The page 1 has not been loaded');

        $this->webClientHelper->assertJsonFromFile(
            $this->assetPages[1],
            self::REQUEST_CONTRACT_DIR . 'successful_brand_assets_for_ecommerce_channel.json'
        );
    }

    /**
     * @When the connector requests all assets of the Brand asset family with the information of a non-existent channel
     */
    public function theConnectorRequestAllAssetsOfTheBrandAssetFamilyWithTheInformationOfANonExistentChannel(): void
    {
        $client = $this->clientFactory->logIn('julia');
        $this->requestContract = 'unprocessable_entity_brand_assets_for_non_existent_channel.json';

        $this->unprocessableEntityResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR . $this->requestContract
        );
    }

    /**
     * @Then the PIM notifies the connector about an error indicating that the provided channel does not exist
     */
    public function thePimNotifiesTheConnectorAboutAnErrorIndicatingThatTheProvidedChannelDoesNotExist(): void
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->unprocessableEntityResponse,
            self::REQUEST_CONTRACT_DIR . $this->requestContract
        );
    }

    /**
     * @Given 3 assets for the Brand asset family with filled attribute values for the English and the French locales
     */
    public function theAssetsForTheBrandAssetFamilyWithFilledAttributeValuesForEnglishAndFrenchLocales()
    {
        $this->findActivatedLocalesByIdentifiers->save(LocaleIdentifier::fromCode('en_US'));
        $this->findActivatedLocalesByIdentifiers->save(LocaleIdentifier::fromCode('fr_FR'));

        for ($i = 1; $i <= 3; $i++) {
            $rawAssetCode = sprintf('brand_%d', $i);
            $assetCode = AssetCode::fromString($rawAssetCode);
            $assetIdentifier = AssetIdentifier::fromString(sprintf('%s_fingerprint', $rawAssetCode));

            Asset::create(
                $assetIdentifier,
                AssetFamilyIdentifier::fromString('brand'),
                $assetCode,
                ValueCollection::fromValues([])
            );

            $connectorAsset = new ConnectorAsset(
                $assetCode,
                [
                    'label' => [
                        [
                            'locale'  => 'en_US',
                            'channel' => null,
                            'value'   => sprintf('English label for %s', $rawAssetCode)
                        ],
                        [
                            'locale'  => 'fr_FR',
                            'channel' => null,
                            'value'   => sprintf('French label for %s', $rawAssetCode)
                        ]
                    ],
                    'description' => [
                        [
                            'locale' => 'en_US',
                            'channel' => 'ecommerce',
                            'data' => sprintf('Description for the brand number %d', $i)
                        ],
                        [
                            'locale' => 'fr_FR',
                            'channel' => 'ecommerce',
                            'data' => sprintf('Description pour la marque numero %d', $i)
                        ]
                    ],
                    'country' => [
                        [
                            'locale' => 'en_US',
                            'channel' => null,
                            'data' => 'Italy'
                        ],
                        [
                            'locale' => 'fr_FR',
                            'channel' => null,
                            'data' => 'Italie'
                        ]
                    ]
                ]
            );

            $this->connectorAssetsByAssetIdentifier[(string) $assetIdentifier] = $connectorAsset;
            $this->findConnectorAssets->save($assetIdentifier, $connectorAsset);
        }

        $assetFamily = AssetFamily::create(
            AssetFamilyIdentifier::fromString('brand'),
            [],
            Image::createEmpty(),
            RuleTemplateCollection::empty()
        );
        $this->assetFamilyRepository->create($assetFamily);
    }

    /**
     * @When the connector requests all assets of the Brand asset family with the information in English
     */
    public function theConnectorRequestsAllAssetsOfTheBrandAssetFamilyWithTheInformationInEnglish()
    {
        $client = $this->clientFactory->logIn('julia');
        $this->assetPages[1] = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR . 'successful_brand_assets_for_english_locale.json'
        );
    }

    /**
     * @Then the PIM returns 3 assets of the Brand asset family with the information in English only
     */
    public function thePimReturnsTheAssetsOfTheBrandAssetFamilyWithTheInformationInEnglishOnly()
    {
        Assert::keyExists($this->assetPages, 1, 'The page 1 has not been loaded');

        $this->webClientHelper->assertJsonFromFile(
            $this->assetPages[1],
            self::REQUEST_CONTRACT_DIR . 'successful_brand_assets_for_english_locale.json'
        );
    }

    /**
     * @When the connector requests all assets of the Brand asset family with the information of a provided locale that does not exist
     */
    public function theConnectorRequestsAllAssetsOfTheBrandAssetFamilyWithTheAttributesValuesOfAProvidedLocaleThatDoesNotExist()
    {
        $this->findActivatedLocalesByIdentifiers->save(LocaleIdentifier::fromCode('en_US'));
        $client = $this->clientFactory->logIn('julia');
        $this->requestContract = 'unprocessable_entity_brand_assets_for_non_existent_locale.json';

        $this->unprocessableEntityResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR . $this->requestContract
        );
    }

    /**
     * @Then the PIM notifies the connector about an error indicating that the provided locale does not exist
     */
    public function thePimNotifiesTheConnectorAboutAnErrorIndicatingThatTheProvidedLocaleDoesNotExist()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->unprocessableEntityResponse,
            self::REQUEST_CONTRACT_DIR . 'unprocessable_entity_brand_assets_for_non_existent_locale.json'
        );
    }

    /**
     * @Given /^([\d]+) assets for the Brand asset family on the Ecommerce channel that are incomplete for the French locale but complete for the English locale$/
     */
    public function assetsForTheBrandAssetFamilyOnTheEcommerceChannelThatAreIncompleteForTheFrenchLocaleButCompleteForTheEnglishLocale(int $numberOfAssets)
    {
        $this->findActivatedLocalesPerChannels->save('ecommerce', ['en_US', 'fr_FR']);
        $this->channelExists->save(ChannelIdentifier::fromCode('ecommerce'));
        $this->findActivatedLocalesByIdentifiers->save(LocaleIdentifier::fromCode('en_US'));
        $this->findActivatedLocalesByIdentifiers->save(LocaleIdentifier::fromCode('fr_FR'));
        $this->loadBrandAssetFamily();
        $this->loadRequiredAttribute();
        $this->loadNotRequiredAttribute();

        for ($i = 1; $i <= $numberOfAssets; $i++) {
            $rawAssetCode = sprintf('incomplete_french_%d', $i);
            $assetCode = AssetCode::fromString($rawAssetCode);
            $assetIdentifier = AssetIdentifier::fromString(sprintf('%s_fingerprint', $rawAssetCode));
            $labelCollection = [
                'en_US' => sprintf('Incomplete french Brand asset number %d', $i)
            ];

            Asset::create(
                $assetIdentifier,
                AssetFamilyIdentifier::fromString('brand'),
                $assetCode,
                ValueCollection::fromValues([
                    Value::create(
                        AttributeIdentifier::fromString('label_brand_fingerprint'),
                        ChannelReference::noReference(),
                        LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                        TextData::fromString(sprintf('Incomplete french Brand asset number %d', $i))
                    ),
                    Value::create(
                        AttributeIdentifier::create('brand', 'required_attribute', 'fingerprint'),
                        ChannelReference::createFromNormalized('ecommerce'),
                        LocaleReference::createFromNormalized('en_US'),
                        TextData::fromString('Required attribute ecommerce en_US')
                    ),
                    Value::create(
                        AttributeIdentifier::create('brand', 'not_required_attribute', 'fingerprint'),
                        ChannelReference::createFromNormalized('ecommerce'),
                        LocaleReference::createFromNormalized('en_US'),
                        TextData::fromString('Not Required attribute ecommerce en_US')
                    ),
                    Value::create(
                        AttributeIdentifier::create('brand', 'not_required_attribute', 'fingerprint'),
                        ChannelReference::createFromNormalized('ecommerce'),
                        LocaleReference::createFromNormalized('fr_FR'),
                        TextData::fromString('Not Required attribute ecommerce fr_FR')
                    )
                ])
            );

            $connectorAsset = new ConnectorAsset(
                $assetCode,
                [
                    'label' => [
                        [
                            'locale'  => 'en_US',
                            'channel' => null,
                            'value'   => $labelCollection['en_US']
                        ],
                    ],
                    'required_attribute' => [
                        [
                            'locale'  => 'en_US',
                            'channel' => 'ecommerce',
                            'data'    => 'Required attribute ecommerce en_US'
                        ]
                    ],
                    'not_required_attribute' => [
                        [
                            'locale'  => 'en_US',
                            'channel' => 'ecommerce',
                            'data'    => 'Not required attribute ecommerce en_US'
                        ],
                        [
                            'locale'  => 'fr_FR',
                            'channel' => 'ecommerce',
                            'data'    => 'Not required attribute ecommerce fr_FR'
                        ]
                    ]
                ]
            );

            $this->findConnectorAssets->save($assetIdentifier, $connectorAsset);
        }
    }

    /**
     * @Given /^([\d]+) assets for the Brand asset family on the Ecommerce channel that are complete for the French locale but that are incomplete for the English locale$/
     */
    public function assetsForTheBrandAssetFamilyOnTheEcommerceChannelThatAreCompleteForTheFrenchLocaleButThatAreIncompleteForTheEnglishLocale(int $numberOfAssets)
    {
        for ($i = 1; $i <= $numberOfAssets; $i++) {
            $rawAssetCode = sprintf('incomplete_english_%d', $i);
            $assetCode = AssetCode::fromString($rawAssetCode);
            $assetIdentifier = AssetIdentifier::fromString(sprintf('%s_fingerprint', $rawAssetCode));
            $labelCollection = [
                'en_US' => sprintf('Incomplete english Brand asset number %d', $i)
            ];

            Asset::create(
                $assetIdentifier,
                AssetFamilyIdentifier::fromString('brand'),
                $assetCode,
                ValueCollection::fromValues([
                    Value::create(
                        AttributeIdentifier::fromString('label_brand_fingerprint'),
                        ChannelReference::noReference(),
                        LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                        TextData::fromString(sprintf('Incomplete english Brand asset number %d', $i))
                    ),
                    Value::create(
                        AttributeIdentifier::create('brand', 'required_attribute', 'fingerprint'),
                        ChannelReference::createFromNormalized('ecommerce'),
                        LocaleReference::createFromNormalized('fr_FR'),
                        TextData::fromString('Required attribute ecommerce fr_FR')
                    ),
                    Value::create(
                        AttributeIdentifier::create('brand', 'not_required_attribute', 'fingerprint'),
                        ChannelReference::createFromNormalized('ecommerce'),
                        LocaleReference::createFromNormalized('en_US'),
                        TextData::fromString('Not Required attribute ecommerce en_US')
                    ),
                    Value::create(
                        AttributeIdentifier::create('brand', 'not_required_attribute', 'fingerprint'),
                        ChannelReference::createFromNormalized('ecommerce'),
                        LocaleReference::createFromNormalized('fr_FR'),
                        TextData::fromString('Not Required attribute ecommerce fr_FR')
                    )
                ])
            );

            $connectorAsset = new ConnectorAsset(
                $assetCode,
                [
                    'label' => [
                        [
                            'locale'  => 'en_US',
                            'channel' => null,
                            'value'   => $labelCollection['en_US']
                        ],
                    ],
                    'required_attribute' => [
                        [
                            'locale'  => 'fr_FR',
                            'channel' => 'ecommerce',
                            'data'    => 'Required attribute ecommerce fr_FR'
                        ]
                    ],
                    'not_required_attribute' => [
                        [
                            'locale'  => 'en_US',
                            'channel' => 'ecommerce',
                            'data'    => 'Not required attribute ecommerce en_US'
                        ],
                        [
                            'locale'  => 'fr_FR',
                            'channel' => 'ecommerce',
                            'data'    => 'Not required attribute ecommerce fr_FR'
                        ]
                    ]
                ]
            );

            $this->findConnectorAssets->save($assetIdentifier, $connectorAsset);
        }
    }

    /**
     * @Given /^([\d]+) assets for the Brand asset family on the Ecommerce channel that are both complete for the French and the English locale$/
     */
    public function assetsForTheBrandAssetFamilyOnTheEcommerceChannelThatAreBothCompleteForTheFrenchAndTheEnglishLocale(int $numberOfAssets)
    {
        $this->findRequiredValueKeyCollectionForChannelAndLocales->setActivatedChannels(['ecommerce']);
        $this->findRequiredValueKeyCollectionForChannelAndLocales->setActivatedLocales(['en_US', 'fr_FR']);

        for ($i = 1; $i <= $numberOfAssets; $i++) {
            $rawAssetCode = sprintf('complete_brand_asset_%d', $i);
            $assetCode = AssetCode::fromString($rawAssetCode);
            $assetIdentifier = AssetIdentifier::fromString(sprintf('%s_fingerprint', $rawAssetCode));
            $labelCollection = [
                'en_US' => sprintf('Complete Brand asset number %d', $i)
            ];

            Asset::create(
                $assetIdentifier,
                AssetFamilyIdentifier::fromString('brand'),
                $assetCode,
                ValueCollection::fromValues([
                    Value::create(
                        AttributeIdentifier::fromString('label_brand_fingerprint'),
                        ChannelReference::noReference(),
                        LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                        TextData::fromString(sprintf('Complete Brand asset number %d', $i))
                    ),
                    Value::create(
                        AttributeIdentifier::create('brand', 'required_attribute', 'fingerprint'),
                        ChannelReference::createFromNormalized('ecommerce'),
                        LocaleReference::createFromNormalized('en_US'),
                        TextData::fromString('Required attribute ecommerce en_US')
                    ),
                    Value::create(
                        AttributeIdentifier::create('brand', 'required_attribute', 'fingerprint'),
                        ChannelReference::createFromNormalized('ecommerce'),
                        LocaleReference::createFromNormalized('fr_FR'),
                        TextData::fromString('Required attribute ecommerce fr_FR')
                    ),
                    Value::create(
                        AttributeIdentifier::create('brand', 'not_required_attribute', 'fingerprint'),
                        ChannelReference::createFromNormalized('ecommerce'),
                        LocaleReference::createFromNormalized('en_US'),
                        TextData::fromString('Not Required attribute ecommerce en_US')
                    ),
                    Value::create(
                        AttributeIdentifier::create('brand', 'not_required_attribute', 'fingerprint'),
                        ChannelReference::createFromNormalized('ecommerce'),
                        LocaleReference::createFromNormalized('fr_FR'),
                        TextData::fromString('Not Required attribute ecommerce fr_FR')
                    )
                ])
            );

            $connectorAsset = new ConnectorAsset(
                $assetCode,
                [
                    'label' => [
                        [
                            'locale'  => 'en_US',
                            'channel' => null,
                            'value'   => $labelCollection['en_US']
                        ],
                    ],
                    'required_attribute' => [
                        [
                            'locale'  => 'fr_FR',
                            'channel' => 'ecommerce',
                            'data'    => 'Required attribute ecommerce fr_FR'
                        ],
                        [
                            'locale'  => 'en_US',
                            'channel' => 'ecommerce',
                            'data'    => 'Required attribute ecommerce en_US'
                        ]
                    ],
                    'not_required_attribute' => [
                        [
                            'locale'  => 'en_US',
                            'channel' => 'ecommerce',
                            'data'    => 'Not required attribute ecommerce en_US'
                        ],
                        [
                            'locale'  => 'fr_FR',
                            'channel' => 'ecommerce',
                            'data'    => 'Not required attribute ecommerce fr_FR'
                        ]
                    ]
                ]
            );

            $this->findConnectorAssets->save($assetIdentifier, $connectorAsset);
        }
    }

    /**
     * @When the connector requests all complete assets of the Brand asset family on the Ecommerce channel for the French and English locales
     */
    public function theConnectorRequestsAllCompleteAssetsOfTheBrandAssetFamilyOnTheEcommerceChannelForTheFrenchAndEnglishLocales()
    {
        $client = $this->clientFactory->logIn('julia');
        $this->assetPages[1] = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR . 'successful_complete_brand_assets.json'
        );
    }

    /**
     * @Then the PIM returns the 2 complete assets of the Brand asset family on the Ecommerce channel for the French and English locales
     */
    public function thePimReturnsThe2CompleteAssetsOfTheBrandAssetFamilyOnTheEcommerceChannelForTheFrenchAndEnglishLocales()
    {
        Assert::keyExists($this->assetPages, 1, 'The page 1 has not been loaded');

        $this->webClientHelper->assertJsonFromFile(
            $this->assetPages[1],
            self::REQUEST_CONTRACT_DIR . 'successful_complete_brand_assets.json'
        );
    }

    /**
     * @When /^the connector requests all complete assets of the Brand asset family on a channel that does not exist$/
     */
    public function theConnectorRequestsAllCompleteAssetsOfTheBrandAssetFamilyOnAChannelThatDoesNotExist()
    {
        $client = $this->clientFactory->logIn('julia');
        $this->requestContract = 'unprocessable_entity_brand_assets_filtered_on_completeness_for_a_non_existent_channel.json';

        $this->unprocessableEntityResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR . $this->requestContract
        );
    }

    /**
     * @When /^the connector requests all complete assets of the Brand asset family on the Ecommerce channel for a not activated locale$/
     */
    public function theConnectorRequestsAllCompleteAssetsOfTheBrandAssetFamilyOnTheEcommerceChannelForANotActivatedLocale()
    {
        $client = $this->clientFactory->logIn('julia');
        $this->requestContract = 'unprocessable_entity_brand_assets_filtered_on_completeness_for_a_non_existent_locale.json';

        $this->unprocessableEntityResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR . $this->requestContract
        );
    }

    private function loadBrandAssetFamily(): void
    {
        $assetFamily = AssetFamily::create(
            AssetFamilyIdentifier::fromString('brand'),
            [],
            Image::createEmpty(),
            RuleTemplateCollection::empty()
        );

        $this->assetFamilyRepository->create($assetFamily);
    }

    private function loadRequiredAttribute(): void
    {
        $attribute = TextAttribute::createText(
            AttributeIdentifier::create('brand', 'required_attribute', 'fingerprint'),
            AssetFamilyIdentifier::fromString('brand'),
            AttributeCode::fromString('required_attribute'),
            LabelCollection::fromArray(['en_US' => 'Required attribute']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(155),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );

        $this->attributeRepository->create($attribute);
    }

    private function loadNotRequiredAttribute(): void
    {
        $attribute = TextAttribute::createText(
            AttributeIdentifier::create('brand', 'not_required_attribute', 'fingerprint'),
            AssetFamilyIdentifier::fromString('brand'),
            AttributeCode::fromString('not_required_attribute'),
            LabelCollection::fromArray(['en_US' => 'Not required attribute']),
            AttributeOrder::fromInteger(3),
            AttributeIsRequired::fromBoolean(false),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(155),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );

        $this->attributeRepository->create($attribute);
    }

    /**
     * @Given /^2 assets for the Brand asset family that were last updated on the 10th of October (\d+)$/
     */
    public function assetsForTheBrandAssetFamilyThatWereLastUpdatedOnThe10thOfOctober()
    {
        $this->dateRepository->setCurrentDate(new \DateTime('2018-10-10'));

        for ($i = 4; $i >= 2; $i--) {
            $rawAssetCode = sprintf('brand_%d', $i);
            $assetCode = AssetCode::fromString($rawAssetCode);
            $assetIdentifier = AssetIdentifier::fromString(sprintf('%s_fingerprint', $rawAssetCode));

            Asset::create(
                $assetIdentifier,
                AssetFamilyIdentifier::fromString('brand_test'),
                $assetCode,
                ValueCollection::fromValues([
                    Value::create(
                        AttributeIdentifier::fromString('label_brand_fingerprint'),
                        ChannelReference::noReference(),
                        LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                        TextData::fromString('test')
                    ),
                ])
            );

            $connectorAsset = new ConnectorAsset(
                $assetCode,
                []
            );

            $this->connectorAssetsByAssetIdentifier[(string) $assetIdentifier] = $connectorAsset;
            $this->findConnectorAssets->save($assetIdentifier, $connectorAsset);
        }

        $assetFamily = AssetFamily::create(
            AssetFamilyIdentifier::fromString('brand_test'),
            [],
            Image::createEmpty(),
            RuleTemplateCollection::empty()
        );

        $this->assetFamilyRepository->create($assetFamily);
    }

    /**
     * @Given /^2 assets for the Brand asset family that were updated on the 15th of October 2018$/
     */
    public function assetsForTheBrandAssetFamilyThatWereUpdatedOnThe15thOfOctober()
    {
        $this->dateRepository->setCurrentDate(new \DateTime('2018-10-15'));

        for ($i = 2; $i >= 0; $i--) {
            $rawAssetCode = sprintf('brand_%d', $i);
            $assetCode = AssetCode::fromString($rawAssetCode);
            $assetIdentifier = AssetIdentifier::fromString(sprintf('%s_fingerprint', $rawAssetCode));

            Asset::create(
                $assetIdentifier,
                AssetFamilyIdentifier::fromString('brand_test'),
                $assetCode,
                ValueCollection::fromValues([
                    Value::create(
                        AttributeIdentifier::fromString('label_brand_fingerprint'),
                        ChannelReference::noReference(),
                        LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                        TextData::fromString('test')
                    ),
                ])
            );

            $connectorAsset = new ConnectorAsset(
                $assetCode,
                []
            );

            $this->connectorAssetsByAssetIdentifier[(string) $assetIdentifier] = $connectorAsset;
            $this->findConnectorAssets->save($assetIdentifier, $connectorAsset);
        }
    }

    /**
     * @When /^the connector requests all assets of the Brand asset family updated since the 14th of October 2018$/
     */
    public function theConnectorRequestsAllAssetsOfTheBrandAssetFamilyUpdatedSinceThe14thOfOctober()
    {
        $client = $this->clientFactory->logIn('julia');

        $this->updatedSinceResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR . 'updated_since_brand_assets_page_1.json'
        );
    }

    /**
     * @Then /^the PIM returns the 2 assets of the Brand asset family that were updated on the 15th of October 2018$/
     */
    public function thePIMReturnsTheAssetsOfTheBrandAssetFamilyThatWereUpdatedOnThe15thOfOctober()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->updatedSinceResponse,
            self::REQUEST_CONTRACT_DIR . 'updated_since_brand_assets_page_1.json'
        );
    }

    /**
     * @When /^the connector requests assets that were updated since a date that does not have the right format$/
     */
    public function theConnectorRequestsAssetsThatWereUpdatedSinceADateThatDoesNotHaveTheRightFormat()
    {
        $client = $this->clientFactory->logIn('julia');

        $this->updatedSinceWrongFormatResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR . 'updated_entity_brand_assets_for_wrong_format.json'
        );
    }

    /**
     * @Then /^the PIM notifies the connector about an error indicating that the date format is not the expected one$/
     */
    public function thePIMNotifiesTheConnectorAboutAnErrorIndicatingThatTheDateFormatIsNotTheExpectedOne()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->updatedSinceWrongFormatResponse,
            self::REQUEST_CONTRACT_DIR . 'updated_entity_brand_assets_for_wrong_format.json'
        );
    }
}
