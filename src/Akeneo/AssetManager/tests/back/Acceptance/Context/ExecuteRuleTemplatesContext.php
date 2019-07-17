<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Acceptance\Context;

use Akeneo\AssetManager\Application\Asset\CreateAsset\CreateAssetHandler;
use Akeneo\AssetManager\Application\Asset\ExecuteRuleTemplates\CompiledRule;
use Akeneo\AssetManager\Application\Asset\ExecuteRuleTemplates\CompiledRuleRunnerInterface;
use Akeneo\AssetManager\Common\Fake\CompiledRuleRunnerSpy;
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
use PHPUnit\Framework\Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ExecuteRuleTemplatesContext implements Context
{
    private const ASSET_FAMILY_IDENTIFIER = 'packshot';
    private const ASSET_CODE = 'sofa';

    // Attributes
    private const SKU = 'sku';
    private const CATEGORY_FIELD = 'category_field';
    private const CATEGORY = 'category';
    private const PRODUCT_SIMPLE_LINK = 'product_simple_link';
    private const PRODUCT_MULTIPLE_LINK = 'product_multiple_link';
    private const SKU_DATA = 'MY SKU';
    private const CATEGORY_FIELD_DATA = 'category';
    private const CATEGORY_DATA = 'couch';
    private const PRODUCT_SIMPLE_LINK_DATA = 'asset_simple_link';
    private const PRODUCT_MULTIPLE_LINK_DATA = 'asset_multiple_link';
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

    public function __construct(
        AssetRepositoryInterface $assetRepository,
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        AttributeRepositoryInterface $attributeRepository,
        CreateAssetHandler $createAssetHandler,
        CompiledRuleRunnerInterface $compiledRuleRunnerSpy,
        ValidatorInterface $validator,
        ExceptionContext $exceptionContext
    ) {
        $this->assetRepository = $assetRepository;
        $this->assetFamilyRepository = $assetFamilyRepository;
        $this->attributeRepository = $attributeRepository;
        $this->createAssetHandler = $createAssetHandler;
        $this->validator = $validator;
        $this->exceptionContext = $exceptionContext;
        $this->compiledRuleRunnerSpy = $compiledRuleRunnerSpy;
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
                            'conditions' => [
                                [
                                    'field'    => self::SKU,
                                    'operator' => Operators::EQUALS,
                                    'value'    => '1111111304'
                                ]
                            ],
                            'actions'    => [
                                [
                                    'type'  => 'add',
                                    'field' => 'new_asset_multiple_link',
                                    'items' => ['1212121212']
                                ],
                                [
                                    'type'  => 'set',
                                    'field' => 'new_asset_single_link',
                                    'value' => '123123123123'
                                ]
                            ]
                        ],
                        [
                            'conditions' => [
                                [
                                    'field'    => self::SKU,
                                    'operator' => Operators::EQUALS,
                                    'value'    => '1111111304'
                                ]
                            ],
                            'actions'    => [
                                [
                                    'type'  => 'add',
                                    'field' => 'new_asset_multiple_link',
                                    'items' => ['1212121212']
                                ],
                                [
                                    'type'  => 'set',
                                    'field' => 'new_asset_single_link',
                                    'value' => '123123123123'
                                ]
                            ]
                        ]
                    ]
                )
            )
        );
    }

    /**
     * @When /^I create an asset for this family$/
     */
    public function iCreateAnAssetForThisFamily(): void
    {
        $this->assetRepository->create(
            Asset::create(
                AssetIdentifier::fromString('sofa_canape_finger'),
                AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER),
                AssetCode::fromString(self::ASSET_CODE),
                ValueCollection::fromValues([])
            )
        );
    }

    /**
     * @Then /^some rules have been executed to link the asset to products$/
     */
    public function someRulesHaveBeenExecutedToLinkTheAssetToProducts(): void
    {
        $this->compiledRuleRunnerSpy->assertHasRunTimes(2);
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
     * @Then /^there is no rule executed to link the asset to products$/
     */
    public function thereIsNoRuleExecutedToLinkTheAssetToProducts()
    {
        $this->compiledRuleRunnerSpy->assertHasRunTimes(0);
    }

    /**
     * @Given /^an asset family with a rule template having a dynamic patterns depending on the asset values$/
     */
    public function anAssetFamilyWithARuleTemplateHavingADynamicValuesDependingOnTheAssetValues()
    {
        $this->createTextAttribute(self::SKU);
        $this->createTextAttribute(self::CATEGORY_FIELD);
        $this->createTextAttribute(self::CATEGORY);
        $this->createTextAttribute(self::PRODUCT_SIMPLE_LINK);
        $this->createTextAttribute(self::PRODUCT_MULTIPLE_LINK);
        $this->assetFamilyRepository->create(
            AssetFamily::create(
                AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER),
                [],
                Image::createEmpty(),
                RuleTemplateCollection::createFromProductLinkRules(
                    [
                        [
                            'conditions' => [
                                [
                                    'field'    => self::SKU,
                                    'operator' => Operators::EQUALS,
                                    'value'    => '{{sku}}'
                                ],
                                [
                                    'field'    => '{{category_field}}',
                                    'operator' => Operators::EQUALS,
                                    'value'    => '{{category}}'
                                ]
                            ],
                            'actions'    => [
                                [
                                    'type'  => 'add',
                                    'field' => '{{product_simple_link}}',
                                    'items' => ['{{code}}']
                                ],
                                [
                                    'type'  => 'set',
                                    'field' => '{{product_multiple_link}}',
                                    'value' => '{{code}}'
                                ],
                                [
                                    'type'  => 'set',
                                    'field' => 'another_asset_single_link',
                                    'value' => '{{sku}}'
                                ]
                            ]
                        ]
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
                                self::PRODUCT_SIMPLE_LINK,
                                self::FINGERPRINT
                            ),
                            ChannelReference::noReference(),
                            LocaleReference::noReference(),
                            TextData::fromString(self::PRODUCT_SIMPLE_LINK_DATA)
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
                        )
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
                        'value'    => self::SKU_DATA
                    ],
                    [
                        'field'    => self::CATEGORY_FIELD_DATA,
                        'operator' => Operators::EQUALS,
                        'value'    => self::CATEGORY_DATA
                    ]
                ],
                [
                    [
                        'type'  => 'add',
                        'field' => self::PRODUCT_SIMPLE_LINK_DATA,
                        'items' => ['sofa']
                    ],
                    [
                        'type'  => 'set',
                        'field' => self::PRODUCT_MULTIPLE_LINK_DATA,
                        'value' => 'sofa'
                    ],
                    [
                        'type'  => 'set',
                        'field' => 'another_asset_single_link',
                        'value' => self::SKU_DATA
                    ]
                ]
            )
        );
    }
}
