<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Acceptance\Context;

use Akeneo\AssetManager\Application\Asset\CreateAsset\CreateAssetHandler;
use Akeneo\AssetManager\Application\Asset\LinkAssets\CompiledRuleRunnerInterface;
use Akeneo\AssetManager\Application\Asset\LinkAssets\LinkAssetCommand;
use Akeneo\AssetManager\Application\Asset\LinkAssets\LinkAssetsHandler;
use Akeneo\AssetManager\Application\Asset\LinkAssets\LinkMultipleAssetsCommand;
use Akeneo\AssetManager\Common\Fake\CompiledRuleRunnerSpy;
use Akeneo\AssetManager\Common\Fake\ProductLinkRuleLauncherSpy;
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
use Akeneo\AssetManager\Domain\Model\AssetFamily\CompiledRule;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplateCollection;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
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
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Akeneo\AssetManager\Domain\Repository\AssetRepositoryInterface;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Behat\Behat\Context\Context;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LinkAssetsContext implements Context
{
    private const ASSET_FAMILY_IDENTIFIER = 'packshot';
    private const ASSET_CODE = 'sofa';

    // Attributes
    private const SKU = 'sku';
    private const CATEGORY_FIELD = 'category_field';
    private const CATEGORY = 'category';
    private const PRODUCT_MULTIPLE_LINK = 'product_multiple_link';
    private const ANOTHER_PRODUCT_MULTIPLE_LINK = 'another_product_multiple_link';
    private const SKU_DATA = 'MY SKU';
    private const CATEGORY_FIELD_DATA = 'category';
    private const CATEGORY_DATA = 'couch';
    private const PRODUCT_MULTIPLE_LINK_DATA = 'asset_multiple_link';
    private const ANOTHER_PRODUCT_MULTIPLE_LINK_DATA = 'another_asset_multiple_link';
    private const FINGERPRINT = 'fingerprint';

    /** @var AssetRepositoryInterface */
    private $assetRepository;

    /** @var AssetFamilyRepositoryInterface */
    private $assetFamilyRepository;

    /** @var CreateAssetHandler */
    private $createAssetHandler;

    /** @var ValidatorInterface */
    private $validator;

    /** @var ExceptionContext */
    private $exceptionContext;

    /** @var CompiledRuleRunnerSpy */
    private $compiledRuleRunnerSpy;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var ProductLinkRuleLauncherSpy */
    private $productLinkRuleLauncherSpy;

    /** @var LinkAssetsHandler */
    private $linkAssetsHandler;

    public function __construct(
        AssetRepositoryInterface $assetRepository,
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        AttributeRepositoryInterface $attributeRepository,
        CreateAssetHandler $createAssetHandler,
        CompiledRuleRunnerInterface $compiledRuleRunnerSpy,
        ValidatorInterface $validator,
        ExceptionContext $exceptionContext,
        ProductLinkRuleLauncherSpy $productLinkRuleLauncherSpy,
        LinkAssetsHandler $linkAssetsHandler
    ) {
        $this->assetRepository = $assetRepository;
        $this->assetFamilyRepository = $assetFamilyRepository;
        $this->attributeRepository = $attributeRepository;
        $this->createAssetHandler = $createAssetHandler;
        $this->validator = $validator;
        $this->exceptionContext = $exceptionContext;
        $this->compiledRuleRunnerSpy = $compiledRuleRunnerSpy;
        $this->productLinkRuleLauncherSpy = $productLinkRuleLauncherSpy;
        $this->linkAssetsHandler = $linkAssetsHandler;
    }

    /**
     * @Given /^an asset family with some rule templates$/
     */
    public function anAssetFamilyWithSomeRuleTemplates(): void
    {
        $this->assetFamilyRepository->create(
            AssetFamily::create(
                AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER),
                [],
                Image::createEmpty(),
                RuleTemplateCollection::createFromProductLinkRules(
                    [
                        [
                            'product_selections' => [
                                [
                                    'field'    => self::SKU,
                                    'operator' => Operators::EQUALS,
                                    'value'    => '1111111304',
                                ],
                            ],
                            'assign_assets_to'   => [
                                [
                                    'mode'      => 'add',
                                    'attribute' => 'new_asset_multiple_link',
                                ],
                                [
                                    'mode'      => 'replace',
                                    'attribute' => 'new_asset_single_link',
                                ],
                            ],
                        ],
                        [
                            'product_selections' => [
                                [
                                    'field'    => self::SKU,
                                    'operator' => Operators::EQUALS,
                                    'value'    => '1111111304',
                                ],
                            ],
                            'assign_assets_to'   => [
                                [
                                    'mode'      => 'add',
                                    'attribute' => 'new_asset_multiple_link',
                                ],
                            ],
                        ],
                    ]
                )
            )
        );
    }

    /**
     * @When /^I link some assets to some products using this rule template$/
     */
    public function iLinkSomeAssetsToSomeProductsUsingThisRuleTemplate(): void
    {
        $this->createAsset(self::ASSET_FAMILY_IDENTIFIER, 'house');
        $this->createAsset(self::ASSET_FAMILY_IDENTIFIER, 'flower');
        $this->linkAssets(self::ASSET_FAMILY_IDENTIFIER, ['house', 'flower']);
    }

    /**
     * @Then /^a job has been launched to link assets to products$/
     */
    public function aJobHasBeenLaunchedToLinkAssetsToProducts(): void
    {
        $this->productLinkRuleLauncherSpy->assertHasRunForAsset(self::ASSET_FAMILY_IDENTIFIER, 'house');
        $this->productLinkRuleLauncherSpy->assertHasRunForAsset(self::ASSET_FAMILY_IDENTIFIER, 'flower');
    }

    /**
     * @Given /^an asset family with no rule templates$/
     */
    public function anAssetFamilyWithNoRuleTemplates()
    {
        $this->assetFamilyRepository->create(
            AssetFamily::create(
                AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER),
                [],
                Image::createEmpty(),
                RuleTemplateCollection::empty()
            )
        );
    }

    /**
     * @Given /^an asset family with a rule template having a dynamic patterns depending on the asset values$/
     */
    public function anAssetFamilyWithARuleTemplateHavingADynamicValuesDependingOnTheAssetValues()
    {
        $this->createTextAttribute(self::SKU);
        $this->createTextAttribute(self::CATEGORY_FIELD);
        $this->createTextAttribute(self::CATEGORY);
        $this->createTextAttribute(self::PRODUCT_MULTIPLE_LINK);
        $this->createTextAttribute(self::ANOTHER_PRODUCT_MULTIPLE_LINK);
        $this->assetFamilyRepository->create(
            AssetFamily::create(
                AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER),
                [],
                Image::createEmpty(),
                RuleTemplateCollection::createFromProductLinkRules(
                    [
                        [
                            'product_selections' => [
                                [
                                    'field'    => self::SKU,
                                    'operator' => Operators::EQUALS,
                                    'value'    => '{{sku}}',
                                ],
                                [
                                    'field'    => '{{category_field}}',
                                    'operator' => Operators::EQUALS,
                                    'value'    => '{{category}}',
                                ],
                            ],
                            'assign_assets_to'   => [
                                [
                                    'mode'      => 'add',
                                    'attribute' => '{{product_multiple_link}}',
                                ],
                                [
                                    'mode'      => 'set',
                                    'attribute' => '{{another_product_multiple_link}}',
                                ],
                            ],
                        ],
                    ]
                )
            )
        );
    }

    private function createTextAttribute($attributeCode): void
    {
        static $order = 4;
        $this->attributeRepository->create(
            TextAttribute::createText(
                AttributeIdentifier::create(self::ASSET_FAMILY_IDENTIFIER, $attributeCode, self::FINGERPRINT),
                AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER),
                AttributeCode::fromString($attributeCode),
                LabelCollection::fromArray([]),
                AttributeOrder::fromInteger($order++),
                AttributeIsRequired::fromBoolean(false),
                AttributeValuePerChannel::fromBoolean(false),
                AttributeValuePerLocale::fromBoolean(false),
                AttributeMaxLength::fromInteger(100),
                AttributeValidationRule::none(),
                AttributeRegularExpression::createEmpty()
            )
        );
    }

    /**
     * @When /^I create an asset for this family having values for the dynamic patterns$/
     */
    public function iCreateAnAssetForThisFamilyHavingValuesForTheDynamicPatterns()
    {
        $this->assetRepository->create(
            Asset::create(
                AssetIdentifier::fromString('sofa_canape_finger'),
                AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER),
                AssetCode::fromString(self::ASSET_CODE),
                ValueCollection::fromValues(
                    [
                        Value::create(
                            AttributeIdentifier::create(
                                self::ASSET_FAMILY_IDENTIFIER,
                                self::SKU,
                                self::FINGERPRINT
                            ),
                            ChannelReference::noReference(),
                            LocaleReference::noReference(),
                            TextData::fromString(self::SKU_DATA)
                        ),
                        Value::create(
                            AttributeIdentifier::create(
                                self::ASSET_FAMILY_IDENTIFIER,
                                self::CATEGORY_FIELD,
                                self::FINGERPRINT
                            ),
                            ChannelReference::noReference(),
                            LocaleReference::noReference(),
                            TextData::fromString(self::CATEGORY_FIELD_DATA)
                        ),
                        Value::create(
                            AttributeIdentifier::create(
                                self::ASSET_FAMILY_IDENTIFIER,
                                self::CATEGORY,
                                self::FINGERPRINT
                            ),
                            ChannelReference::noReference(),
                            LocaleReference::noReference(),
                            TextData::fromString(self::CATEGORY_DATA)
                        ),
                        Value::create(
                            AttributeIdentifier::create(
                                self::ASSET_FAMILY_IDENTIFIER,
                                self::PRODUCT_MULTIPLE_LINK,
                                self::FINGERPRINT
                            ),
                            ChannelReference::noReference(),
                            LocaleReference::noReference(),
                            TextData::fromString(self::PRODUCT_MULTIPLE_LINK_DATA)
                        ),
                        Value::create(
                            AttributeIdentifier::create(
                                self::ASSET_FAMILY_IDENTIFIER,
                                self::ANOTHER_PRODUCT_MULTIPLE_LINK,
                                self::FINGERPRINT
                            ),
                            ChannelReference::noReference(),
                            LocaleReference::noReference(),
                            TextData::fromString(self::ANOTHER_PRODUCT_MULTIPLE_LINK_DATA)
                        ),
                    ]
                )
            )
        );
    }

    /**
     * @Then /^there is a rule executed to link this asset that takes into account the dynamic values$/
     */
    public function thereIsARuleExecutedToLinkThisAssetThatTakesIntoAccountTheDynamicValues()
    {
        $this->compiledRuleRunnerSpy->assertHasRunTimes(1);
        $this->compiledRuleRunnerSpy->assertLastCompiledRule(
            new CompiledRule(
                [
                    [
                        'field'    => self::SKU,
                        'operator' => Operators::EQUALS,
                        'value'    => self::SKU_DATA,
                        'channel'  => null,
                        'locale'   => null,
                    ],
                    [
                        'field'    => self::CATEGORY_FIELD_DATA,
                        'operator' => Operators::EQUALS,
                        'value'    => self::CATEGORY_DATA,
                        'channel'  => null,
                        'locale'   => null,
                    ],
                ],
                [
                    [
                        'type'    => 'add',
                        'field'   => self::PRODUCT_MULTIPLE_LINK_DATA,
                        'items'   => ['sofa'],
                        'channel' => null,
                        'locale'  => null,
                    ],
                    [
                        'type'    => 'set',
                        'field'   => self::ANOTHER_PRODUCT_MULTIPLE_LINK_DATA,
                        'items'   => ['sofa'],
                        'channel' => null,
                        'locale'  => null,
                    ],
                ]
            )
        );
    }

    private function createAsset(string $assetFamilyIdentifier, string $assetCode): void
    {
        $assetIdentifier = sprintf('%s%s_finger', $assetCode, $assetFamilyIdentifier);
        $this->assetRepository->create(
            Asset::create(
                AssetIdentifier::fromString($assetIdentifier),
                AssetFamilyIdentifier::fromString($assetFamilyIdentifier),
                AssetCode::fromString($assetCode),
                ValueCollection::fromValues([])
            )
        );
    }

    private function linkAssets(string $assetFamilyIdentifier, array $assetsToLink): void
    {
        $linkMultipleAssetsCommand = new LinkMultipleAssetsCommand();
        $linkMultipleAssetsCommand->linkAssetCommands = array_map(
            function (string $assetCode) use ($assetFamilyIdentifier) {
                $LinkAssetCommand = new LinkAssetCommand();
                $LinkAssetCommand->assetCode = $assetCode;
                $LinkAssetCommand->assetFamilyIdentifier = $assetFamilyIdentifier;

                return $LinkAssetCommand;
            },
            $assetsToLink
        );
        $this->linkAssetsHandler->handle($linkMultipleAssetsCommand);
    }
}
