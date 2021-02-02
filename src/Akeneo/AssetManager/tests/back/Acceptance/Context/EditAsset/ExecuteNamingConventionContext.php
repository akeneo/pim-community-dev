<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Acceptance\Context\EditAsset;

use Akeneo\AssetManager\Acceptance\Context\ConstraintViolationsContext;
use Akeneo\AssetManager\Acceptance\Context\ExceptionContext;
use Akeneo\AssetManager\Application\Asset\EditAsset\EditAssetHandler;
use Akeneo\AssetManager\Application\Asset\ExecuteNamingConvention\EditAssetCommandFactory;
use Akeneo\AssetManager\Application\Asset\ExecuteNamingConvention\ExecuteNamingConvention;
use Akeneo\AssetManager\Application\AssetFamily\CreateAssetFamily\CreateAssetFamilyCommand;
use Akeneo\AssetManager\Application\AssetFamily\CreateAssetFamily\CreateAssetFamilyHandler;
use Akeneo\AssetManager\Common\Fake\InMemoryFileExists;
use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ChannelReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\FileData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\LocaleReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\Value;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ValueCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\NamingConvention\NamingConvention;
use Akeneo\AssetManager\Domain\Model\AssetFamily\NamingConvention\NamingConventionInterface;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeAllowedExtensions;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeDecimalsAllowed;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsReadOnly;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeLimit;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeMaxFileSize;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFile\MediaType;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFileAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLink\MediaType as LinkMediaType;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLink\Prefix;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLink\Suffix;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLinkAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\NumberAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\TextAttribute;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Query\Attribute\ValueKey;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Akeneo\AssetManager\Domain\Repository\AssetRepositoryInterface;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use Behat\Behat\Context\Context;
use PHPUnit\Framework\Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
class ExecuteNamingConventionContext implements Context
{
    private const ASSET_FAMILY_IDENTIFIER = 'packshot';
    private const FINGERPRINT = 'fingerprint';

    private const MEDIA_FILE_ATTRIBUTE_CODE = 'the_media_file';
    private const TEXT_ATTRIBUTE_CODE = 'title';
    private const LOCALIZABLE_TEXT_ATTRIBUTE_CODE = 'localizabletitle';
    private const NUMBER_ATTRIBUTE_CODE = 'length';
    private const MEDIA_LINK_ATTRIBUTE_CODE = 'link';

    private const ASSET_CODE = 'otherTitle_14_otherLink';

    private const PATTERN = '/(?P<title>[a-zA-Z0-9\s]+)_(?P<length>\d+)_(?P<link>\w+)/';
    private const PATTERN_WITH_TITLE_ONLY = '/^(?P<title>[^\.]+)/';
    private const UNMATCHED_PATTERN = '/(?P<title>[a-zA-Z0-9\s]+)\|(?P<length>\d+)\|(?P<link>\w+)/';
    private const PATTERN_WITH_LOCALIZABLE_TARGET = '/(?P<localizabletitle>[a-zA-Z0-9\s]+)_(?P<length>\d+)_(?P<link>\w+)/';
    private const ORIGINAL_FILENAME = 'title_12_the_link-useless part.png';

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var ValidatorInterface */
    private $validator;

    /** @var AssetRepositoryInterface */
    private $assetRepository;

    /** @var AssetFamilyRepositoryInterface */
    private $assetFamilyRepository;

    /** @var CreateAssetFamilyHandler */
    private $createAssetFamilyHandler;

    /** @var InMemoryFileExists */
    private $fileExists;

    /** @var EditAssetCommandFactory */
    private $editAssetCommandFactory;

    /** @var EditAssetHandler */
    private $editAssetHandler;

    /** @var ExceptionContext */
    private $exceptionContext;

    /** @var ConstraintViolationsContext */
    private $violationsContext;

    /** @var ExecuteNamingConvention */
    private $executeNamingConvention;

    /** @var null|string */
    private $createdAssetCode = null;

    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        ValidatorInterface $validator,
        AssetRepositoryInterface $assetRepository,
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        CreateAssetFamilyHandler $createAssetFamilyHandler,
        InMemoryFileExists $fileExists,
        EditAssetCommandFactory $editAssetCommandFactory,
        EditAssetHandler $editAssetHandler,
        ExceptionContext $exceptionContext,
        ConstraintViolationsContext $violationContext,
        ExecuteNamingConvention $executeNamingConvention
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->validator = $validator;
        $this->assetRepository = $assetRepository;
        $this->assetFamilyRepository = $assetFamilyRepository;
        $this->createAssetFamilyHandler = $createAssetFamilyHandler;
        $this->fileExists = $fileExists;
        $this->editAssetCommandFactory = $editAssetCommandFactory;
        $this->editAssetHandler = $editAssetHandler;
        $this->exceptionContext = $exceptionContext;
        $this->violationsContext = $violationContext;
        $this->executeNamingConvention = $executeNamingConvention;
    }

    /**
     * @Given /^an asset family with some attributes and a naming convention with media file source$/
     */
    public function anAssetFamilyWithSomeAttributesAndANamingConventionWithMediaFileSource()
    {
        $this->createAssetFamilyWithAttributesAndNamingConvention(
            self::ASSET_FAMILY_IDENTIFIER,
            NamingConvention::createFromNormalized([
                'source' => ['property' => self::MEDIA_FILE_ATTRIBUTE_CODE, 'channel' => null, 'locale' => null],
                'pattern' => self::PATTERN,
                'abort_asset_creation_on_error' => true,
            ])
        );
    }

    /**
     * @Given /^an asset family with some attributes and a naming convention with code source$/
     */
    public function anAssetFamilyWithSomeAttributesAndANamingConventionWithCodeSource()
    {
        $this->createAssetFamilyWithAttributesAndNamingConvention(
            self::ASSET_FAMILY_IDENTIFIER,
            NamingConvention::createFromNormalized([
                'source' => ['property' => 'code', 'channel' => null, 'locale' => null],
                'pattern' => self::PATTERN,
                'abort_asset_creation_on_error' => true,
            ])
        );
    }

    /**
     * @Given /^an asset family with some attributes and a naming convention with unknown source$/
     */
    public function anAssetFamilyWithSomeAttributesAndANamingConventionWithUnknownSource()
    {
        $this->createAssetFamilyWithAttributesAndNamingConvention(
            self::ASSET_FAMILY_IDENTIFIER,
            NamingConvention::createFromNormalized([
                'source' => ['property' => 'unknown', 'channel' => null, 'locale' => null],
                'pattern' => self::PATTERN,
                'abort_asset_creation_on_error' => true,
            ])
        );
    }

    /**
     * @Given /^an asset family with some attributes and a naming convention with localizable target and non strict mode$/
     */
    public function anAssetFamilyWithSomeAttributesAndANamingConventionWithLocalizableTargetAndNonStrictMode()
    {
        $this->createAssetFamilyWithAttributesAndNamingConvention(
            self::ASSET_FAMILY_IDENTIFIER,
            NamingConvention::createFromNormalized([
                'source' => ['property' => self::MEDIA_FILE_ATTRIBUTE_CODE, 'channel' => null, 'locale' => null],
                'pattern' => self::PATTERN_WITH_LOCALIZABLE_TARGET,
                'abort_asset_creation_on_error' => false,
            ])
        );
    }

    /**
     * @Given /^an asset family with some attributes and a naming convention with unmatched pattern and non strict mode$/
     */
    public function anAssetFamilyWithSomeAttributesAndANamingConventionWithUnmatchedPatternAndNonStrictMode()
    {
        $this->createAssetFamilyWithAttributesAndNamingConvention(
            self::ASSET_FAMILY_IDENTIFIER,
            NamingConvention::createFromNormalized([
                'source' => ['property' => self::MEDIA_FILE_ATTRIBUTE_CODE, 'channel' => null, 'locale' => null],
                'pattern' => self::UNMATCHED_PATTERN,
                'abort_asset_creation_on_error' => false,
            ])
        );
    }

    /**
     * @Given /^an asset family with some attributes and a naming convention with unmatched pattern and strict mode$/
     */
    public function anAssetFamilyWithSomeAttributesAndANamingConventionWithUnmatchedPatternAndStrictMode()
    {
        $this->createAssetFamilyWithAttributesAndNamingConvention(
            self::ASSET_FAMILY_IDENTIFIER,
            NamingConvention::createFromNormalized([
                'source' => ['property' => self::MEDIA_FILE_ATTRIBUTE_CODE, 'channel' => null, 'locale' => null],
                'pattern' => self::UNMATCHED_PATTERN,
                'abort_asset_creation_on_error' => true,
            ])
        );
    }

    /**
     * @When /^an asset is created with valid values for naming convention execution$/
     */
    public function AnAssetIsCreatedWithValidValuesForNamingConventionExecution(): void
    {
        $this->createdAssetCode = 'the_code';
        $this->createAsset($this->createdAssetCode);

        $attribute = $this->attributeRepository->getByCodeAndAssetFamilyIdentifier(
            AttributeCode::fromString(self::MEDIA_FILE_ATTRIBUTE_CODE),
            AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER)
        );
        $this->executeNamingConventionCommand([
            'asset_family_identifier' => self::ASSET_FAMILY_IDENTIFIER,
            'code' => $this->createdAssetCode,
            'values' => [
                [
                    'attribute' => $attribute->getIdentifier()->stringValue(),
                    'channel'   => null,
                    'locale'    => null,
                    'data'      => [
                        'originalFilename' => self::ORIGINAL_FILENAME,
                    ],
                ],
            ],
        ]);
    }

    /**
     * @When /^an asset is created with valid code for naming convention execution$/
     */
    public function AnAssetIsCreatedWithValidCodeForNamingConventionExecution(): void
    {
        $this->createdAssetCode = self::ASSET_CODE;
        $this->createAsset($this->createdAssetCode);

        $this->executeNamingConventionCommand([
            'asset_family_identifier' => self::ASSET_FAMILY_IDENTIFIER,
            'code' => $this->createdAssetCode,
            'values' => [],
        ]);
    }

    /**
     * @Then /^the asset should contain the new values based on media file/
     */
    public function theAssetShouldContainTheNewValuesBasedOnMediaFile(): void
    {
        $asset = $this->assetRepository->getByAssetFamilyAndCode(
            AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER),
            AssetCode::fromString($this->createdAssetCode)
        );

        $value = $asset->findValue($this->buildValueKey(self::MEDIA_FILE_ATTRIBUTE_CODE));
        Assert::assertInstanceOf(Value::class, $value);
        Assert::assertEquals('title_12_the_link-useless part.png', $value->getData()->normalize()['originalFilename']);

        $value = $asset->findValue($this->buildValueKey(self::TEXT_ATTRIBUTE_CODE));
        Assert::assertInstanceOf(Value::class, $value);
        Assert::assertEquals('title', $value->getData()->normalize());

        $value = $asset->findValue($this->buildValueKey(self::NUMBER_ATTRIBUTE_CODE));
        Assert::assertInstanceOf(Value::class, $value);
        Assert::assertEquals('12', $value->getData()->normalize());

        $value = $asset->findValue($this->buildValueKey(self::MEDIA_LINK_ATTRIBUTE_CODE));
        Assert::assertInstanceOf(Value::class, $value);
        Assert::assertEquals('the_link', $value->getData()->normalize());
    }

    /**
     * @Then /^the asset should contain the new values based on code/
     */
    public function theAssetShouldContainTheNewValuesBasedOnCode(): void
    {
        $asset = $this->assetRepository->getByAssetFamilyAndCode(
            AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER),
            AssetCode::fromString($this->createdAssetCode)
        );

        $value = $asset->findValue($this->buildValueKey(self::MEDIA_FILE_ATTRIBUTE_CODE));
        Assert::assertInstanceOf(Value::class, $value);
        Assert::assertEquals('title_12_the_link-useless part.png', $value->getData()->normalize()['originalFilename']);

        $value = $asset->findValue($this->buildValueKey(self::TEXT_ATTRIBUTE_CODE));
        Assert::assertInstanceOf(Value::class, $value);
        Assert::assertEquals('otherTitle', $value->getData()->normalize());

        $value = $asset->findValue($this->buildValueKey(self::NUMBER_ATTRIBUTE_CODE));
        Assert::assertInstanceOf(Value::class, $value);
        Assert::assertEquals('14', $value->getData()->normalize());

        $value = $asset->findValue($this->buildValueKey(self::MEDIA_LINK_ATTRIBUTE_CODE));
        Assert::assertInstanceOf(Value::class, $value);
        Assert::assertEquals('otherLink', $value->getData()->normalize());
    }

    /**
     * @Then /^the asset should be unchanged/
     */
    public function theAssetShouldBeUnchanged(): void
    {
        $asset = $this->assetRepository->getByAssetFamilyAndCode(
            AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER),
            AssetCode::fromString($this->createdAssetCode)
        );

        $value = $asset->findValue($this->buildValueKey(self::MEDIA_FILE_ATTRIBUTE_CODE));
        Assert::assertInstanceOf(Value::class, $value);
        Assert::assertEquals('title_12_the_link-useless part.png', $value->getData()->normalize()['originalFilename']);

        $value = $asset->findValue($this->buildValueKey(self::TEXT_ATTRIBUTE_CODE));
        Assert::assertNull($value);

        $value = $asset->findValue($this->buildValueKey(self::NUMBER_ATTRIBUTE_CODE));
        Assert::assertNull($value);

        $value = $asset->findValue($this->buildValueKey(self::MEDIA_LINK_ATTRIBUTE_CODE));
        Assert::assertNull($value);
    }

    private function executeNamingConventionCommand(array $normalizedCommand): void
    {
        try {
            $command = $this->editAssetCommandFactory->create($normalizedCommand);
            $violations = $this->validator->validate($command);
            if ($violations->count() > 0) {
                $this->violationsContext->addViolations($violations);

                return;
            }

            ($this->editAssetHandler)($command);
        } catch (\Exception $e) {
            $this->exceptionContext->setException($e);
        }
    }

    private function createAssetFamilyWithAttributesAndNamingConvention(
        string $assetFamilyIdentifier,
        NamingConventionInterface $namingConvention
    ): void {
        $createCommand = new CreateAssetFamilyCommand(
            $assetFamilyIdentifier,
            [],
            [],
            []
        );

        $violations = $this->validator->validate($createCommand);
        if ($violations->count() > 0) {
            throw new \LogicException(sprintf('Cannot create asset family: %s', $violations->get(0)->getMessage()));
        }

        ($this->createAssetFamilyHandler)($createCommand);

        $assetFamily = $this->assetFamilyRepository->getByIdentifier(AssetFamilyIdentifier::fromString($assetFamilyIdentifier));
        $assetFamily->updateNamingConvention($namingConvention);
        $this->assetFamilyRepository->update($assetFamily);

        $this->attributeRepository->create(
            MediaFileAttribute::create(
                AttributeIdentifier::create(
                    self::ASSET_FAMILY_IDENTIFIER,
                    self::MEDIA_FILE_ATTRIBUTE_CODE,
                    self::FINGERPRINT
                ),
                AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER),
                AttributeCode::fromString(self::MEDIA_FILE_ATTRIBUTE_CODE),
                LabelCollection::fromArray([]),
                AttributeOrder::fromInteger(2),
                AttributeIsRequired::fromBoolean(false),
                AttributeIsReadOnly::fromBoolean(false),
                AttributeValuePerChannel::fromBoolean(false),
                AttributeValuePerLocale::fromBoolean(false),
                AttributeMaxFileSize::noLimit(),
                AttributeAllowedExtensions::fromList(AttributeAllowedExtensions::ALL_ALLOWED),
                MediaType::fromString(MediaType::IMAGE)
            )
        );
        $this->attributeRepository->create(
            TextAttribute::createText(
                AttributeIdentifier::create(
                    self::ASSET_FAMILY_IDENTIFIER,
                    self::TEXT_ATTRIBUTE_CODE,
                    self::FINGERPRINT
                ),
                AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER),
                AttributeCode::fromString(self::TEXT_ATTRIBUTE_CODE),
                LabelCollection::fromArray([]),
                AttributeOrder::fromInteger(3),
                AttributeIsRequired::fromBoolean(false),
                AttributeIsReadOnly::fromBoolean(false),
                AttributeValuePerChannel::fromBoolean(false),
                AttributeValuePerLocale::fromBoolean(false),
                AttributeMaxLength::fromInteger(255),
                AttributeValidationRule::none(),
                AttributeRegularExpression::createEmpty()
            )
        );
        $this->attributeRepository->create(
            NumberAttribute::create(
                AttributeIdentifier::create(
                    self::ASSET_FAMILY_IDENTIFIER,
                    self::NUMBER_ATTRIBUTE_CODE,
                    self::FINGERPRINT
                ),
                AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER),
                AttributeCode::fromString(self::NUMBER_ATTRIBUTE_CODE),
                LabelCollection::fromArray([]),
                AttributeOrder::fromInteger(4),
                AttributeIsRequired::fromBoolean(false),
                AttributeIsReadOnly::fromBoolean(false),
                AttributeValuePerChannel::fromBoolean(false),
                AttributeValuePerLocale::fromBoolean(false),
                AttributeDecimalsAllowed::fromBoolean(true),
                AttributeLimit::limitless(),
                AttributeLimit::limitless()
            )
        );
        $this->attributeRepository->create(
            MediaLinkAttribute::create(
                AttributeIdentifier::create(
                    self::ASSET_FAMILY_IDENTIFIER,
                    self::MEDIA_LINK_ATTRIBUTE_CODE,
                    self::FINGERPRINT
                ),
                AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER),
                AttributeCode::fromString(self::MEDIA_LINK_ATTRIBUTE_CODE),
                LabelCollection::fromArray([]),
                AttributeOrder::fromInteger(5),
                AttributeIsRequired::fromBoolean(false),
                AttributeIsReadOnly::fromBoolean(false),
                AttributeValuePerChannel::fromBoolean(false),
                AttributeValuePerLocale::fromBoolean(false),
                Prefix::empty(),
                Suffix::empty(),
                LinkMediaType::fromString(LinkMediaType::OTHER)
            )
        );
        $this->attributeRepository->create(
            TextAttribute::createText(
                AttributeIdentifier::create(
                    self::ASSET_FAMILY_IDENTIFIER,
                    self::LOCALIZABLE_TEXT_ATTRIBUTE_CODE,
                    self::FINGERPRINT
                ),
                AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER),
                AttributeCode::fromString(self::LOCALIZABLE_TEXT_ATTRIBUTE_CODE),
                LabelCollection::fromArray([]),
                AttributeOrder::fromInteger(6),
                AttributeIsRequired::fromBoolean(false),
                AttributeIsReadOnly::fromBoolean(false),
                AttributeValuePerChannel::fromBoolean(false),
                AttributeValuePerLocale::fromBoolean(true),
                AttributeMaxLength::fromInteger(255),
                AttributeValidationRule::none(),
                AttributeRegularExpression::createEmpty()
            )
        );
    }

    private function createAsset(string $assetCode)
    {
        $file = new FileInfo();
        $file->setOriginalFilename(self::ORIGINAL_FILENAME);
        $file->setKey('123');

        $this->fileExists->save($file->getKey());
        $fileValue = Value::create(
            AttributeIdentifier::create(
                self::ASSET_FAMILY_IDENTIFIER,
                self::MEDIA_FILE_ATTRIBUTE_CODE,
                self::FINGERPRINT
            ),
            ChannelReference::noReference(),
            LocaleReference::noReference(),
            FileData::createFromFileinfo($file, \DateTimeImmutable::createFromFormat(\DateTimeImmutable::ISO8601, '2019-11-22T15:16:21+0000'))
        );
        $this->assetRepository->create(
            Asset::create(
                AssetIdentifier::create(self::ASSET_FAMILY_IDENTIFIER, $assetCode, self::FINGERPRINT),
                AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER),
                AssetCode::fromString($assetCode),
                ValueCollection::fromValues([$fileValue])
            )
        );
    }

    private function buildValueKey(string $attributeCode, string $channel = null, string $locale = null): ValueKey
    {
        $attribute = $this->attributeRepository->getByCodeAndAssetFamilyIdentifier(
            AttributeCode::fromString($attributeCode),
            AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER)
        );

        return ValueKey::create(
            $attribute->getIdentifier(),
            ChannelReference::createFromNormalized($channel),
            LocaleReference::createFromNormalized($locale)
        );
    }

    /**
     * @Given /^an asset with valid values for naming convention$/
     */
    public function anAssetWithValidValuesForNamingConvention()
    {
        $this->createdAssetCode = 'the_code';
        $this->createAsset($this->createdAssetCode);

        $attribute = $this->attributeRepository->getByCodeAndAssetFamilyIdentifier(
            AttributeCode::fromString(self::MEDIA_FILE_ATTRIBUTE_CODE),
            AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER)
        );
        $this->executeNamingConventionCommand([
            'asset_family_identifier' => self::ASSET_FAMILY_IDENTIFIER,
            'code' => $this->createdAssetCode,
            'values' => [
                [
                    'attribute' => $attribute->getIdentifier()->stringValue(),
                    'channel' => null,
                    'locale' => null,
                    'data' => [
                        'originalFilename' => self::ORIGINAL_FILENAME,
                    ],
                ],
            ],
        ]);
    }

    /**
     * @When /^the naming convention is updated$/
     */
    public function theNamingConventionIsUpdated()
    {
        $assetFamily = $this->assetFamilyRepository->getByIdentifier(AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER));
        $assetFamily = $assetFamily->withNamingConvention(
            NamingConvention::createFromNormalized([
                'source' => ['property' => self::MEDIA_FILE_ATTRIBUTE_CODE, 'channel' => null, 'locale' => null],
                'pattern' => self::PATTERN_WITH_TITLE_ONLY,
                'abort_asset_creation_on_error' => true,
            ])
        );

        $this->assetFamilyRepository->update($assetFamily);
    }

    /**
     * @Given /^I request the naming convention execution$/
     */
    public function iRequestTheNamingConventionExecution()
    {
        try {
            $this->executeNamingConvention->executeOnAsset(
                AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER),
                AssetIdentifier::create(self::ASSET_FAMILY_IDENTIFIER, $this->createdAssetCode, self::FINGERPRINT)
            );
        } catch (\Exception $exception) {
            $this->exceptionContext->setException($exception);
        }
    }

    /**
     * @Then /^the asset should contain the updated values based on media file$/
     */
    public function theAssetShouldContainTheUpdatedValuesBasedOnMediaFile()
    {
        $asset = $this->assetRepository->getByAssetFamilyAndCode(
            AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER),
            AssetCode::fromString($this->createdAssetCode)
        );

        $value = $asset->findValue($this->buildValueKey(self::MEDIA_FILE_ATTRIBUTE_CODE));
        Assert::assertInstanceOf(Value::class, $value);
        Assert::assertEquals('title_12_the_link-useless part.png', $value->getData()->normalize()['originalFilename']);

        $value = $asset->findValue($this->buildValueKey(self::TEXT_ATTRIBUTE_CODE));
        Assert::assertInstanceOf(Value::class, $value);
        Assert::assertEquals('title_12_the_link-useless part', $value->getData()->normalize());
    }

    /**
     * @Given /^I request the naming convention execution on a missing asset$/
     */
    public function iRequestTheNamingConventionExecutionOnAMissingAsset()
    {
        try {
            $this->executeNamingConvention->executeOnAsset(
                AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER),
                AssetIdentifier::create(self::ASSET_FAMILY_IDENTIFIER, 'the_code', self::FINGERPRINT)
            );
        } catch (\Exception $exception) {
            $this->exceptionContext->setException($exception);
        }
    }

    /**
     * @When /^the naming convention is updated with an invalid configuration$/
     */
    public function theNamingConventionIsUpdatedWithAnInvalidConfiguration()
    {
        $assetFamily = $this->assetFamilyRepository->getByIdentifier(AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER));
        $assetFamily = $assetFamily->withNamingConvention(
            NamingConvention::createFromNormalized([
                'source' => ['property' => self::MEDIA_FILE_ATTRIBUTE_CODE, 'channel' => null, 'locale' => null],
                'pattern' => '/^(?P<none>[^\.]+)/',
                'abort_asset_creation_on_error' => true,
            ])
        );

        $this->assetFamilyRepository->update($assetFamily);
    }
}
