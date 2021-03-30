<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Acceptance\Context\EditAsset;

use Akeneo\AssetManager\Acceptance\Context\ConstraintViolationsContext;
use Akeneo\AssetManager\Acceptance\Context\ExceptionContext;
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditAssetCommandFactory;
use Akeneo\AssetManager\Application\Asset\EditAsset\EditAssetHandler;
use Akeneo\AssetManager\Common\Fake\InMemoryAssetRepository;
use Akeneo\AssetManager\Common\Fake\InMemoryAttributeRepository;
use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ChannelReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\LocaleReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\MediaLinkData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\Value;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ValueCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplateCollection;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsReadOnly;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLink\MediaType;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLink\Prefix;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLink\Suffix;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLinkAttribute;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Query\Attribute\ValueKey;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Akeneo\AssetManager\Domain\Repository\AssetRepositoryInterface;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;
use Behat\Behat\Context\Context;
use PHPUnit\Framework\Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class EditMediaLinkContext implements Context
{
    private const ASSET_FAMILY_IDENTIFIER = 'designer';
    private const ATTRIBUTE_CODE = 'website';
    private const ATTRIBUTE_IDENTIFIER = 'website_designer_fingerprint';
    private const ASSET_CODE = 'stark';
    private const FINGERPRINT = 'fingerprint';
    private const NEW_URL = 'house_2345112';
    private const OLD_URL = 'garden_5124';

    /** @var AssetFamilyRepositoryInterface */
    private $assetFamilyRepository;

    /** @var InMemoryAttributeRepository */
    private $attributeRepository;

    /** @var InMemoryAssetRepository */
    private $assetRepository;

    /** @var EditAssetCommandFactory */
    private $editAssetCommandFactory;

    /** @var EditAssetHandler */
    private $editAssetHandler;

    /** @var ValidatorInterface */
    private $validator;

    /** @var ExceptionContext */
    private $exceptionContext;

    /** @var ConstraintViolationsContext */
    private $violationsContext;

    public function __construct(
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        AttributeRepositoryInterface $attributeRepository,
        AssetRepositoryInterface $assetRepository,
        EditAssetCommandFactory $editAssetCommandFactory,
        EditAssetHandler $editAssetHandler,
        ValidatorInterface $validator,
        ExceptionContext $exceptionContext,
        ConstraintViolationsContext $violationsContext
    ) {
        $this->assetFamilyRepository = $assetFamilyRepository;
        $this->attributeRepository = $attributeRepository;
        $this->assetRepository = $assetRepository;
        $this->editAssetCommandFactory = $editAssetCommandFactory;
        $this->editAssetHandler = $editAssetHandler;
        $this->exceptionContext = $exceptionContext;
        $this->validator = $validator;
        $this->violationsContext = $violationsContext;
    }

    /**
     * @Given /^an asset family with an media_link attribute and an asset belonging to this asset family$/
     */
    public function aAssetFamilyWithAnMediaLinkAttributeAndAAssetBelongingToThisAssetFamily(): void
    {
        $this->createAssetFamily();
        $this->createMediaLinkAttribute();
        $this->createAsset();
    }

    /**
     * @When /^the user updates the mediaLink value of the asset$/
     */
    public function theUserUpdatesTheMediaLinkValueOfTheAsset(): void
    {
        $editCommand = $this->editAssetCommandFactory->create(
            [
                'asset_family_identifier' => self::ASSET_FAMILY_IDENTIFIER,
                'code'                        => self::ASSET_CODE,
                'labels'                      => [],
                'values'                      => [
                    [
                        'attribute' => self::ATTRIBUTE_IDENTIFIER,
                        'channel'   => null,
                        'locale'    => null,
                        'data'      => self::NEW_URL,
                    ],
                ],
            ]
        );

        $violations = $this->validator->validate($editCommand);
        if ($violations->count() > 0) {
            $this->violationsContext->addViolations($violations);

            return;
        }

        try {
            ($this->editAssetHandler)($editCommand);
        } catch (\Exception $e) {
            $this->exceptionContext->setException($e);
        }
    }

    /**
     * @Then /^the asset should have the mediaLink value for this attribute$/
     */
    public function theAssetShouldHaveTheMediaLinkValueForThisAttribute(): void
    {
        $this->violationsContext->assertThereIsNoViolations();
        $this->exceptionContext->assertThereIsNoExceptionThrown();

        $asset = $this->assetRepository->getByAssetFamilyAndCode(
            AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER),
            AssetCode::fromString(self::ASSET_CODE)
        );
        $value = $asset->findValue(
            ValueKey::create(
                AttributeIdentifier::create(
                    self::ASSET_FAMILY_IDENTIFIER,
                    self::ATTRIBUTE_CODE,
                    self::FINGERPRINT
                ),
                ChannelReference::noReference(),
                LocaleReference::noReference()
            )
        );

        Assert::assertNotNull($value);
        Assert::assertSame(self::NEW_URL, $value->getData()->normalize());
    }

    private function createAssetFamily(): void
    {
        $assetFamily = AssetFamily::create(
            AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER),
            [],
            Image::createEmpty(),
            RuleTemplateCollection::empty()
        );
        $this->assetFamilyRepository->create($assetFamily);
    }

    private function createMediaLinkAttribute(): void
    {
        $attribute = MediaLinkAttribute::create(
            AttributeIdentifier::create(
                self::ASSET_FAMILY_IDENTIFIER,
                self::ATTRIBUTE_CODE,
                self::FINGERPRINT
            ),
            AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER),
            AttributeCode::fromString(self::ATTRIBUTE_CODE),
            LabelCollection::fromArray([]),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(false),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            Prefix::createEmpty(),
            Suffix::createEmpty(),
            MediaType::fromString('image')
        );
        $this->attributeRepository->create($attribute);
    }

    private function createAsset(): void
    {
        $this->assetRepository->create(
            Asset::create(
                AssetIdentifier::create(self::ASSET_FAMILY_IDENTIFIER, self::ASSET_CODE, self::FINGERPRINT),
                AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER),
                AssetCode::fromString(self::ASSET_CODE),
                ValueCollection::fromValues([
                    Value::create(
                        AttributeIdentifier::create(
                            self::ASSET_FAMILY_IDENTIFIER,
                            self::ATTRIBUTE_CODE,
                            self::FINGERPRINT
                        ),
                        ChannelReference::noReference(),
                        LocaleReference::noReference(),
                        MediaLinkData::fromString(self::OLD_URL)
                    ),
                ])
            )
        );
    }
}
