<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Acceptance\Context;

use Akeneo\AssetManager\Application\Attribute\EditAttribute\CommandFactory\EditAttributeCommandFactoryInterface;
use Akeneo\AssetManager\Application\Attribute\EditAttribute\EditAttributeHandler;
use Akeneo\AssetManager\Common\Fake\InMemoryFindActivatedLocalesByIdentifiers;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeAllowedExtensions;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeDecimalsAllowed;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsReadOnly;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRichTextEditor;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeLimit;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeMaxFileSize;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOption\AttributeOption;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOption\OptionCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFile\MediaType as MediaFileMediaType;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFileAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLink\MediaType as MediaLinkMediaType;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLink\Prefix;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLink\Suffix;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLinkAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\NumberAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\OptionAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\OptionCollectionAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\TextAttribute;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifier;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use PHPUnit\Framework\Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class EditAttributeContext implements Context
{
    private AttributeRepositoryInterface $attributeRepository;

    private EditAttributeCommandFactoryInterface $editAttributeCommandFactory;

    private EditAttributeHandler $handler;

    private ValidatorInterface $validator;

    private ExceptionContext $exceptionContext;

    private ConstraintViolationsContext $constraintViolationsContext;

    /** @var array AttributeIdentifier */
    private array $attributeIdentifiers = [];

    private InMemoryFindActivatedLocalesByIdentifiers $activatedLocales;

    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        EditAttributeCommandFactoryInterface $editAttributeCommandFactory,
        EditAttributeHandler $handler,
        ValidatorInterface $validator,
        ConstraintViolationsContext $constraintViolationsContext,
        ExceptionContext $exceptionContext,
        InMemoryFindActivatedLocalesByIdentifiers $activatedLocales
    ) {
        $this->validator = $validator;
        $this->attributeRepository = $attributeRepository;
        $this->editAttributeCommandFactory = $editAttributeCommandFactory;
        $this->handler = $handler;
        $this->exceptionContext = $exceptionContext;
        $this->constraintViolationsContext = $constraintViolationsContext;
        $this->activatedLocales = $activatedLocales;
    }

    /**
     * @Given /^the following text attributes?:$/
     */
    public function theFollowingTextAttributes(TableNode $attributesTable)
    {
        foreach ($attributesTable->getHash() as $attribute) {
            if (isset($attribute['identifier'])) {
                $identifier = AttributeIdentifier::fromString($attribute['identifier']);
            } else {
                $identifier = $this->attributeRepository->nextIdentifier(
                    AssetFamilyIdentifier::fromString($attribute['entity_identifier']),
                    AttributeCode::fromString($attribute['code'])
                );
            }

            $this->attributeRepository->create(
                TextAttribute::createText(
                    $identifier,
                    AssetFamilyIdentifier::fromString($attribute['entity_identifier']),
                    AttributeCode::fromString($attribute['code']),
                    LabelCollection::fromArray(json_decode($attribute['labels'], true)),
                    AttributeOrder::fromInteger((int)$attribute['order']),
                    AttributeIsRequired::fromBoolean((bool)$attribute['required']),
                    AttributeIsReadOnly::fromBoolean((bool) $attribute['read_only']),
                    AttributeValuePerChannel::fromBoolean((bool)$attribute['value_per_channel']),
                    AttributeValuePerLocale::fromBoolean((bool)$attribute['value_per_locale']),
                    AttributeMaxLength::fromInteger((int)$attribute['max_length']),
                    AttributeValidationRule::none(),
                    AttributeRegularExpression::createEmpty()
                )
            );
        }
    }

    /**
     * @Given /^an asset family with a text attribute \'([^\']*)\' and the label \'([^\']*)\' equal to \'([^\']*)\'$/
     */
    public function anAssetFamilyWithATextAttributeAndTheLabelEqualTo(
        string $attributeCode,
        string $localeCode,
        string $label
    ): void {
        $this->activatedLocales->save(LocaleIdentifier::fromCode($localeCode));

        $identifier = AttributeIdentifier::create('dummy_identifier', $attributeCode, md5('fingerprint'));
        $this->attributeIdentifiers['dummy_identifier'][$attributeCode] = $identifier;

        $this->attributeRepository->create(
            TextAttribute::createText(
                $identifier,
                AssetFamilyIdentifier::fromString('dummy_identifier'),
                AttributeCode::fromString($attributeCode),
                LabelCollection::fromArray([$localeCode => $label]),
                AttributeOrder::fromInteger(0),
                AttributeIsRequired::fromBoolean(true),
                AttributeIsReadOnly::fromBoolean(false),
                AttributeValuePerChannel::fromBoolean(true),
                AttributeValuePerLocale::fromBoolean(true),
                AttributeMaxLength::fromInteger(100),
                AttributeValidationRule::none(),
                AttributeRegularExpression::createEmpty()
            )
        );
    }

    /**
     * @Then /^the label \'([^\']*)\' of the \'([^\']*)\' attribute should be \'([^\']*)\'$/
     */
    public function theLabelOfTheAttributeShouldBe(string $localeCode, string $attributeCode, $expectedLabel): void
    {
        $identifier = $this->attributeIdentifiers['dummy_identifier'][$attributeCode];

        $this->constraintViolationsContext->assertThereIsNoViolations();
        $attribute = $this->attributeRepository->getByIdentifier($identifier);
        Assert::assertEquals($expectedLabel, $attribute->getLabel($localeCode));
    }

    /**
     * @Given /^an asset family with a text attribute \'([^\']*)\' non required$/
     */
    public function anAssetFamilyWithATextAttributeNonRequired(string $attributeCode)
    {
        $identifier = AttributeIdentifier::create('dummy_identifier', $attributeCode, md5('fingerprint'));
        $this->attributeIdentifiers['dummy_identifier'][$attributeCode] = $identifier;

        $this->attributeRepository->create(
            TextAttribute::createText(
                $identifier,
                AssetFamilyIdentifier::fromString('dummy_identifier'),
                AttributeCode::fromString($attributeCode),
                LabelCollection::fromArray([]),
                AttributeOrder::fromInteger(0),
                AttributeIsRequired::fromBoolean(false),
                AttributeIsReadOnly::fromBoolean(false),
                AttributeValuePerChannel::fromBoolean(false),
                AttributeValuePerLocale::fromBoolean(false),
                AttributeMaxLength::fromInteger(100),
                AttributeValidationRule::none(),
                AttributeRegularExpression::createEmpty()
            )
        );
    }

    /**
     * @Given /^an asset family with a text attribute \'([^\']*)\' not in read only$/
     */
    public function anAssetFamilyWithATextAttributeNotInReadOnly(string $attributeCode)
    {
        $identifier = AttributeIdentifier::create('dummy_identifier', $attributeCode, md5('fingerprint'));
        $this->attributeIdentifiers['dummy_identifier'][$attributeCode] = $identifier;

        $this->attributeRepository->create(
            TextAttribute::createText(
                $identifier,
                AssetFamilyIdentifier::fromString('dummy_identifier'),
                AttributeCode::fromString($attributeCode),
                LabelCollection::fromArray([]),
                AttributeOrder::fromInteger(0),
                AttributeIsRequired::fromBoolean(false),
                AttributeIsReadOnly::fromBoolean(false),
                AttributeValuePerChannel::fromBoolean(false),
                AttributeValuePerLocale::fromBoolean(false),
                AttributeMaxLength::fromInteger(100),
                AttributeValidationRule::none(),
                AttributeRegularExpression::createEmpty()
            )
        );
    }

    /**
     * @When /^the user sets the \'([^\']*)\' attribute required$/
     */
    public function theUserSetsTheAttributeRequired(string $attributeCode)
    {
        $this->theUserSetsTheIsRequiredPropertyOfTo($attributeCode, "true");
    }

    /**
     * @When /^the user sets the \'([^\']*)\' attribute read only/
     */
    public function theUserSetsTheAttributeReadOnly(string $attributeCode)
    {
        $this->theUserSetsTheIsReadOnlyPropertyOfTo($attributeCode, "true");
    }

    /**
     * @Then /^\'([^\']*)\' should be required$/
     */
    public function thenShouldBeRequired(string $attributeCode)
    {
        $identifier = $this->attributeIdentifiers['dummy_identifier'][$attributeCode];

        $this->constraintViolationsContext->assertThereIsNoViolations();
        $attribute = $this->attributeRepository->getByIdentifier($identifier);
        Assert::assertEquals(true, $attribute->normalize()['is_required']);
    }

    /**
     * @Then /^\'([^\']*)\' should be read only$/
     */
    public function thenShouldBeReadOnly(string $attributeCode)
    {
        $identifier = $this->attributeIdentifiers['dummy_identifier'][$attributeCode];

        $this->constraintViolationsContext->assertThereIsNoViolations();
        $attribute = $this->attributeRepository->getByIdentifier($identifier);
        Assert::assertEquals(true, $attribute->normalize()['is_read_only']);
    }

    /**
     * @Given /^an asset family with a text attribute \'([^\']*)\' and max length (\d+)$/
     */
    public function anAssetFamilyWithATextAttributeAndMaxLength(string $attributeCode, int $maxLength)
    {
        $identifier = AttributeIdentifier::create('dummy_identifier', $attributeCode, md5('fingerprint'));
        $this->attributeIdentifiers['dummy_identifier'][$attributeCode] = $identifier;

        $this->attributeRepository->create(
            TextAttribute::createText(
                $identifier,
                AssetFamilyIdentifier::fromString('dummy_identifier'),
                AttributeCode::fromString($attributeCode),
                LabelCollection::fromArray([]),
                AttributeOrder::fromInteger(0),
                AttributeIsRequired::fromBoolean(false),
                AttributeIsReadOnly::fromBoolean(false),
                AttributeValuePerChannel::fromBoolean(false),
                AttributeValuePerLocale::fromBoolean(false),
                AttributeMaxLength::fromInteger($maxLength),
                AttributeValidationRule::none(),
                AttributeRegularExpression::createEmpty()
            )
        );
    }

    /**
     * @When /^the user changes the max length of \'([^\']*)\' to \'([^\']*)\'$/
     */
    public function theUserChangesTheMaxLengthOfTo(string $attributeCode, string $newMaxLength)
    {
        $identifier = $this->attributeIdentifiers['dummy_identifier'][$attributeCode];

        $updateMaxLength = [
            'identifier' => (string)$identifier,
            'max_length' => json_decode($newMaxLength),
        ];
        $this->updateAttribute($updateMaxLength);
    }

    /**
     * @Then /^\'([^\']*)\' max length should be (\d+)$/
     */
    public function thenMaxLengthShouldBe(string $attributeCode, int $expectedMaxLength)
    {
        $this->constraintViolationsContext->assertThereIsNoViolations();
        $identifier = $this->attributeIdentifiers['dummy_identifier'][$attributeCode];

        $attribute = $this->attributeRepository->getByIdentifier($identifier);
        Assert::assertEquals($expectedMaxLength, $attribute->normalize()['max_length']);
    }

    /**
     * @Given /^an asset family with a media file attribute \'([^\']*)\' and the label \'([^\']*)\' equal to \'([^\']*)\'$/
     */
    public function anAssetFamilyWithAMediaFileAttributeAndTheLabelEqualTo(
        string $attributeCode,
        string $localeCode,
        string $label
    ) {
        $this->activatedLocales->save(LocaleIdentifier::fromCode($localeCode));

        $identifier = AttributeIdentifier::create('dummy_identifier', $attributeCode, md5('fingerprint'));
        $this->attributeIdentifiers['dummy_identifier'][$attributeCode] = $identifier;

        $this->attributeRepository->create(
            MediaFileAttribute::create(
                $identifier,
                AssetFamilyIdentifier::fromString('dummy_identifier'),
                AttributeCode::fromString($attributeCode),
                LabelCollection::fromArray([$localeCode => $label]),
                AttributeOrder::fromInteger(0),
                AttributeIsRequired::fromBoolean(true),
                AttributeIsReadOnly::fromBoolean(false),
                AttributeValuePerChannel::fromBoolean(true),
                AttributeValuePerLocale::fromBoolean(true),
                AttributeMaxFileSize::fromString('210'),
                AttributeAllowedExtensions::fromList(['png']),
                MediaFileMediaType::fromString(MediaFileMediaType::IMAGE)
            )
        );
    }

    /**
     * @Given /^an asset family with a media file attribute \'([^\']*)\' with max file size \'([^\']*)\'$/
     */
    public function anAssetFamilyWithATextAttributeAndMaxFileSize(string $attributeCode, string $maxFileSize): void
    {
        $identifier = AttributeIdentifier::create('dummy_identifier', $attributeCode, md5('fingerprint'));
        $this->attributeIdentifiers['dummy_identifier'][$attributeCode] = $identifier;

        $this->attributeRepository->create(
            MediaFileAttribute::create(
                $identifier,
                AssetFamilyIdentifier::fromString('dummy_identifier'),
                AttributeCode::fromString($attributeCode),
                LabelCollection::fromArray([]),
                AttributeOrder::fromInteger(0),
                AttributeIsRequired::fromBoolean(true),
                AttributeIsReadOnly::fromBoolean(false),
                AttributeValuePerChannel::fromBoolean(true),
                AttributeValuePerLocale::fromBoolean(true),
                AttributeMaxFileSize::fromString($maxFileSize),
                AttributeAllowedExtensions::fromList(['png']),
                MediaFileMediaType::fromString(MediaFileMediaType::IMAGE)
            )
        );
    }

    /**
     * @When /^the user changes the max file size of \'([^\']*)\' to \'([^\']*)\'$/
     */
    public function theUserChangesTheMaxFileSizeOfTo(string $attributeCode, string $newMaxFileSize): void
    {
        $identifier = $this->attributeIdentifiers['dummy_identifier'][$attributeCode];

        $updateMaxFileSize = [
            'identifier'    => (string)$identifier,
            'max_file_size' => json_decode($newMaxFileSize),
        ];
        $this->updateAttribute($updateMaxFileSize);
    }

    /**
     * @Then /^the max file size of \'([^\']*)\' should be \'([^\']*)\'$/
     */
    public function thenTheMaxFileSizeOfShouldBe(string $attributeCode, string $expectedMaxFileSize): void
    {
        $identifier = $this->attributeIdentifiers['dummy_identifier'][$attributeCode];

        $this->constraintViolationsContext->assertThereIsNoViolations();
        $attribute = $this->attributeRepository->getByIdentifier($identifier);
        Assert::assertEquals($expectedMaxFileSize, $attribute->normalize()['max_file_size']);
    }

    /**
     * @Given /^an asset family with a text attribute \'([^\']*)\' and no allowed extensions$/
     */
    public function anAssetFamilyWithATextAttributeAndNoAllowedExtensions(string $attributeCode)
    {
        $this->anAssetFamilyWithAnMediaFileAttributeWithAllowedExtensions($attributeCode, '[]');
    }

    /**
     * @When /^the user changes adds \'([^\']*)\' to the allowed extensions of \'([^\']*)\'$/
     */
    public function theUserChangesAddsToTheAllowedExtensionsOf(string $newAllowedExtension, string $attributeCode)
    {
        $identifier = $this->attributeIdentifiers['dummy_identifier'][$attributeCode];

        $updateAllowedExtensions = [
            'identifier'         => (string)$identifier,
            'allowed_extensions' => json_decode($newAllowedExtension),
        ];
        $this->updateAttribute($updateAllowedExtensions);
    }

    /**
     * @Then /^the \'([^\']*)\' should have \'([^\']*)\' as an allowed extension$/
     */
    public function thenShouldHaveAsAnAllowedExtension(string $attributeCode, string $expectedAllowedExtension)
    {
        $identifier = $this->attributeIdentifiers['dummy_identifier'][$attributeCode];

        $this->constraintViolationsContext->assertThereIsNoViolations();
        $attribute = $this->attributeRepository->getByIdentifier($identifier);
        $expectedAllowedExtension = json_decode($expectedAllowedExtension);
        Assert::assertEquals($expectedAllowedExtension, $attribute->normalize()['allowed_extensions']);
    }

    /**
     * @When /^the user updates the \'([^\']*)\' attribute label with \'([^\']*)\' on the locale \'([^\']*)\'$/
     */
    public function theUserUpdatesTheAttributeLabelWithOnTheLocale1(
        string $attributeCode,
        string $label,
        string $localeCode
    ): void {
        if (isset($this->attributeIdentifiers['dummy_identifier'][$attributeCode])) {
            $identifier = $this->attributeIdentifiers['dummy_identifier'][$attributeCode];
        } else {
            $identifier = 'unknown';
        }

        $label = json_decode($label);
        $localeCode = json_decode($localeCode);
        $updateLabels = [
            'identifier' => (string)$identifier,
            'labels'     => [$localeCode => $label],
        ];
        $this->updateAttribute($updateLabels);
    }

    /**
     * @When /^the user sets the is_required property of \'([^\']*)\' to \'([^\']*)\'$/
     */
    public function theUserSetsTheIsRequiredPropertyOfTo(string $attributeCode, $invalidValue)
    {
        $identifier = $this->attributeIdentifiers['dummy_identifier'][$attributeCode];

        $updateIsRequired = [
            'identifier'  => (string)$identifier,
            'is_required' => json_decode($invalidValue),
        ];
        $this->updateAttribute($updateIsRequired);
    }

    /**
     * @When /^the user sets the is_read_only property of \'([^\']*)\' to \'([^\']*)\'$/
     */
    public function theUserSetsTheIsReadOnlyPropertyOfTo(string $attributeCode, $invalidValue)
    {
        $identifier = $this->attributeIdentifiers['dummy_identifier'][$attributeCode];

        $updateIsRequired = [
            'identifier'  => (string)$identifier,
            'is_read_only' => json_decode($invalidValue),
        ];
        $this->updateAttribute($updateIsRequired);
    }

    /**
     * @Then /^there should be no limit for the max length of \'([^\']*)\'$/
     */
    public function thenThereShouldBeNoLimitForTheMaxLengthOf(string $attributeCode)
    {
        $this->constraintViolationsContext->assertThereIsNoViolations();
        $identifier = $this->attributeIdentifiers['dummy_identifier'][$attributeCode];

        $attribute = $this->attributeRepository->getByIdentifier($identifier);
        Assert::assertEquals(AttributeMaxLength::NO_LIMIT, $attribute->normalize()['max_length']);
    }

    /**
     * @When /^the user changes the max length of \'([^\']*)\' to no limit$/
     */
    public function theUserChangesTheMaxLengthOfToNoLimit(string $attributeCode)
    {
        $this->theUserChangesTheMaxLengthOfTo($attributeCode, 'null');
    }

    /**
     * @When /^the user changes the max file size of \'([^\']*)\' to no limit$/
     */
    public function theUserChangesTheMaxFileSizeOfToNoLimit(string $attributeCode)
    {
        $this->theUserChangesTheMaxFileSizeOfTo($attributeCode, 'null');
    }

    /**
     * @Then /^there should be no limit for the max file size of \'([^\']*)\'$/
     */
    public function thenThereShouldBeNoLimitForTheMaxFileSizeOf(string $attributeCode)
    {
        $identifier = $this->attributeIdentifiers['dummy_identifier'][$attributeCode];

        $this->constraintViolationsContext->assertThereIsNoViolations();
        $attribute = $this->attributeRepository->getByIdentifier($identifier);
        Assert::assertEquals(null, $attribute->normalize()['max_file_size']);
    }

    /**
     * @Given /^an asset family with a media file attribute \'([^\']*)\' non required$/
     */
    public function anAssetFamilyWithAnMediaFileAttributeNonRequired(string $attributeCode)
    {
        $identifier = AttributeIdentifier::create('dummy_identifier', $attributeCode, md5('fingerprint'));
        $this->attributeIdentifiers['dummy_identifier'][$attributeCode] = $identifier;

        $this->attributeRepository->create(
            MediaFileAttribute::create(
                $identifier,
                AssetFamilyIdentifier::fromString('dummy_identifier'),
                AttributeCode::fromString($attributeCode),
                LabelCollection::fromArray([]),
                AttributeOrder::fromInteger(0),
                AttributeIsRequired::fromBoolean(false),
                AttributeIsReadOnly::fromBoolean(false),
                AttributeValuePerChannel::fromBoolean(false),
                AttributeValuePerLocale::fromBoolean(false),
                AttributeMaxFileSize::fromString('200'),
                AttributeAllowedExtensions::fromList(['png']),
                MediaFileMediaType::fromString(MediaFileMediaType::IMAGE)
            )
        );
    }

    /**
     * @Given /^an asset family with a media file attribute \'([^\']*)\' with allowed extensions: \'([^\']*)\'$/
     */
    public function anAssetFamilyWithAnMediaFileAttributeWithAllowedExtensions(
        string $attributeCode,
        string $normalizedExtensions
    ): void {
        $identifier = AttributeIdentifier::create('dummy_identifier', $attributeCode, md5('fingerprint'));
        $this->attributeIdentifiers['dummy_identifier'][$attributeCode] = $identifier;

        $extensions = json_decode($normalizedExtensions);
        $this->attributeRepository->create(
            MediaFileAttribute::create(
                $identifier,
                AssetFamilyIdentifier::fromString('dummy_identifier'),
                AttributeCode::fromString($attributeCode),
                LabelCollection::fromArray([]),
                AttributeOrder::fromInteger(0),
                AttributeIsRequired::fromBoolean(true),
                AttributeIsReadOnly::fromBoolean(false),
                AttributeValuePerChannel::fromBoolean(true),
                AttributeValuePerLocale::fromBoolean(true),
                AttributeMaxFileSize::fromString('200'),
                AttributeAllowedExtensions::fromList($extensions),
                MediaFileMediaType::fromString(MediaFileMediaType::IMAGE)
            )
        );
    }

    /**
     * @Given /^an asset family with a text area attribute \'([^\']*)\'$/
     */
    public function anAssetFamilyWithATextareaAttribute(string $attributeCode)
    {
        $identifier = AttributeIdentifier::create('dummy_identifier', $attributeCode, md5('fingerprint'));
        $this->attributeIdentifiers['dummy_identifier'][$attributeCode] = $identifier;

        $this->attributeRepository->create(
            TextAttribute::createTextarea(
                $identifier,
                AssetFamilyIdentifier::fromString('dummy_identifier'),
                AttributeCode::fromString($attributeCode),
                LabelCollection::fromArray([]),
                AttributeOrder::fromInteger(0),
                AttributeIsRequired::fromBoolean(false),
                AttributeIsReadOnly::fromBoolean(false),
                AttributeValuePerChannel::fromBoolean(false),
                AttributeValuePerLocale::fromBoolean(false),
                AttributeMaxLength::fromInteger(150),
                AttributeIsRichTextEditor::fromBoolean(true)
            )
        );
    }

    /**
     * @When /^the user changes the is text area flag of \'([^\']*)\' to \'([^\']*)\'$/
     */
    public function theUserChangesTheIsTextareaFlagTo(string $attributeCode, string $newIsTextarea)
    {
        $identifier = $this->attributeIdentifiers['dummy_identifier'][$attributeCode];

        $updateIsTextarea = [
            'identifier'  => (string)$identifier,
            'is_textarea' => json_decode($newIsTextarea),
        ];
        $this->updateAttribute($updateIsTextarea);
    }

    /**
     * @Then /^the \'([^\']*)\' attribute should be a simple text$/
     */
    public function theAttributeShouldBeASimpleText(string $attributeCode): void
    {
        $identifier = $this->attributeIdentifiers['dummy_identifier'][$attributeCode];

        $this->constraintViolationsContext->assertThereIsNoViolations();
        $attribute = $this->attributeRepository->getByIdentifier($identifier);
        $normalizedAttribute = $attribute->normalize();
        Assert::assertFalse($normalizedAttribute['is_textarea'], 'isTextarea should be false');
        Assert::assertFalse($normalizedAttribute['is_rich_text_editor'], 'isRichTextEditor should be false');
    }

    /**
     * @Given /^an asset family with a text attribute \'([^\']*)\'$/
     */
    public function anAssetFamilyWithATextAttribute(string $attributeCode)
    {
        $identifier = AttributeIdentifier::create('dummy_identifier', $attributeCode, md5('fingerprint'));
        $this->attributeIdentifiers['dummy_identifier'][$attributeCode] = $identifier;

        $this->attributeRepository->create(
            TextAttribute::createText(
                $identifier,
                AssetFamilyIdentifier::fromString('dummy_identifier'),
                AttributeCode::fromString($attributeCode),
                LabelCollection::fromArray([]),
                AttributeOrder::fromInteger(0),
                AttributeIsRequired::fromBoolean(false),
                AttributeIsReadOnly::fromBoolean(false),
                AttributeValuePerChannel::fromBoolean(false),
                AttributeValuePerLocale::fromBoolean(false),
                AttributeMaxLength::fromInteger(150),
                AttributeValidationRule::none(),
                AttributeRegularExpression::createEmpty()
            )
        );
    }

    /**
     * @Then /^the \'([^\']*)\' attribute should be a text area$/
     */
    public function theAttributeShouldBeATextarea($attributeCode)
    {
        $identifier = $this->attributeIdentifiers['dummy_identifier'][$attributeCode];

        $this->constraintViolationsContext->assertThereIsNoViolations();
        $attribute = $this->attributeRepository->getByIdentifier($identifier);
        $normalizedAttribute = $attribute->normalize();
        Assert::assertTrue($normalizedAttribute['is_textarea'], 'isTextarea should be true');
        Assert::assertEquals(
            AttributeValidationRule::NONE,
            $normalizedAttribute['validation_rule'],
            'validationRule should be none'
        );
        Assert::assertNull($normalizedAttribute['regular_expression'], 'regularExpression should be null');
    }

    /**
     * @Given /^an asset family with a text attribute \'([^\']*)\' with no validation rule$/
     */
    public function anAssetFamilyWithATextAttributeWithNoValidationRule(string $attributeCode)
    {
        $identifier = AttributeIdentifier::create('dummy_identifier', $attributeCode, md5('fingerprint'));
        $this->attributeIdentifiers['dummy_identifier'][$attributeCode] = $identifier;

        $this->attributeRepository->create(
            TextAttribute::createText(
                $identifier,
                AssetFamilyIdentifier::fromString('dummy_identifier'),
                AttributeCode::fromString($attributeCode),
                LabelCollection::fromArray([]),
                AttributeOrder::fromInteger(0),
                AttributeIsRequired::fromBoolean(false),
                AttributeIsReadOnly::fromBoolean(false),
                AttributeValuePerChannel::fromBoolean(false),
                AttributeValuePerLocale::fromBoolean(false),
                AttributeMaxLength::fromInteger(150),
                AttributeValidationRule::none(),
                AttributeRegularExpression::createEmpty()
            )
        );
    }

    /**
     * @When /^the user changes the validation rule of \'([^\']*)\' to \'([^\']*)\'$/
     */
    public function theUserChangesTheValidationRuleOfTo(string $attributeCode, string $newValidationRule)
    {
        $identifier = $this->attributeIdentifiers['dummy_identifier'][$attributeCode];

        $updateValidationRule = [
            'identifier'      => (string)$identifier,
            'validation_rule' => json_decode($newValidationRule),
        ];
        $this->updateAttribute($updateValidationRule);
    }

    /**
     * @Then /^the validation rule of \'([^\']*)\' should be \'([^\']*)\'$/
     */
    public function theValidationRuleOfShouldBe(string $attributeCode, string $validationRule)
    {
        $identifier = $this->attributeIdentifiers['dummy_identifier'][$attributeCode];

        $this->constraintViolationsContext->assertThereIsNoViolations();
        $attribute = $this->attributeRepository->getByIdentifier($identifier);
        $normalizedAttribute = $attribute->normalize();
        Assert::assertEquals($validationRule, $normalizedAttribute['validation_rule']);
    }

    /**
     * @Given /^an asset family with a text attribute \'([^\']*)\' with a regular expression \'([^\']*)\'$/
     */
    public function anAssetFamilyWithATextAttributeWithARegularExpression(
        string $attributeCode,
        string $regularExpression
    ) {
        $identifier = AttributeIdentifier::create('dummy_identifier', $attributeCode, md5('fingerprint'));
        $this->attributeIdentifiers['dummy_identifier'][$attributeCode] = $identifier;

        $this->attributeRepository->create(
            TextAttribute::createText(
                $identifier,
                AssetFamilyIdentifier::fromString('dummy_identifier'),
                AttributeCode::fromString($attributeCode),
                LabelCollection::fromArray([]),
                AttributeOrder::fromInteger(0),
                AttributeIsRequired::fromBoolean(false),
                AttributeIsReadOnly::fromBoolean(false),
                AttributeValuePerChannel::fromBoolean(false),
                AttributeValuePerLocale::fromBoolean(false),
                AttributeMaxLength::fromInteger(150),
                AttributeValidationRule::fromString(AttributeValidationRule::REGULAR_EXPRESSION),
                AttributeRegularExpression::fromString($regularExpression)
            )
        );
    }

    /**
     * @Then /^the regular expression of \'([^\']*)\' should be empty$/
     */
    public function theRegularExpressionOfShouldBeEmpty(string $attributeCode)
    {
        $identifier = $this->attributeIdentifiers['dummy_identifier'][$attributeCode];

        $this->constraintViolationsContext->assertThereIsNoViolations();
        $attribute = $this->attributeRepository->getByIdentifier($identifier);
        $normalizedAttribute = $attribute->normalize();
        Assert::assertFalse($normalizedAttribute['is_textarea'], 'isTextarea should be false');
        Assert::assertNotNull($normalizedAttribute['validation_rule'], 'validationRule should be not be null');
        Assert::assertNull($normalizedAttribute['regular_expression'], 'regularExpression should be null');
    }

    /**
     * @When /^the user changes the regular expression of \'([^\']*)\' to \'([^\']*)\'$/
     */
    public function theUserChangesTheRegularExpressionOfToW09(string $attributeCode, string $newRegularExpression)
    {
        $identifier = $this->attributeIdentifiers['dummy_identifier'][$attributeCode];

        $editRegularExpression = [
            'identifier'         => (string)$identifier,
            'regular_expression' => json_decode($newRegularExpression),
        ];
        $this->updateAttribute($editRegularExpression);
    }

    /**
     * @Then /^the regular expression of \'([^\']*)\' should be \'([^\']*)\'$/
     */
    public function theRegularExpressionOfShouldBeW09(string $attributeCode, string $regularExpression)
    {
        $identifier = $this->attributeIdentifiers['dummy_identifier'][$attributeCode];

        $this->constraintViolationsContext->assertThereIsNoViolations();
        $attribute = $this->attributeRepository->getByIdentifier($identifier);
        $normalizedAttribute = $attribute->normalize();
        Assert::assertFalse($normalizedAttribute['is_textarea'], 'isTextarea should be false');
        Assert::assertEquals(AttributeValidationRule::REGULAR_EXPRESSION, $normalizedAttribute['validation_rule']);
        Assert::assertEquals($regularExpression, $normalizedAttribute['regular_expression']);
    }

    /**
     * @When /^the user removes the regular expression of \'([^\']*)\'$/
     */
    public function theUserRemovesTheRegularExpressionOf(string $attributeCode)
    {
        $identifier = $this->attributeIdentifiers['dummy_identifier'][$attributeCode];

        $removeRegularExpression = [
            'identifier'         => (string)$identifier,
            'regular_expression' => null,
        ];
        $this->updateAttribute($removeRegularExpression);
    }

    /**
     * @Then /^there is no regular expression set on \'([^\']*)\'$/
     */
    public function thereIsNoRegularExpressionSetOn(string $attributeCode)
    {
        $identifier = $this->attributeIdentifiers['dummy_identifier'][$attributeCode];

        $this->constraintViolationsContext->assertThereIsNoViolations();
        $attribute = $this->attributeRepository->getByIdentifier($identifier);
        $normalizedAttribute = $attribute->normalize();
        Assert::assertFalse($normalizedAttribute['is_textarea'], 'isTextarea should be false');
        Assert::assertEquals(AttributeRegularExpression::EMPTY, $normalizedAttribute['regular_expression']);
    }

    /**
     * @When /^the user removes the validation rule of \'([^\']*)\'$/
     */
    public function theUserRemovesTheValidationRuleOf(string $attributeCode)
    {
        $identifier = $this->attributeIdentifiers['dummy_identifier'][$attributeCode];

        $removeValidationRule = [
            'identifier'      => (string)$identifier,
            'validation_rule' => AttributeValidationRule::NONE,
        ];
        $this->updateAttribute($removeValidationRule);
    }

    /**
     * @Then /^there is no validation rule set on \'([^\']*)\'$/
     */
    public function thereIsNoValidationRuleSetOn(string $attributeCode)
    {
        $identifier = $this->attributeIdentifiers['dummy_identifier'][$attributeCode];

        $this->constraintViolationsContext->assertThereIsNoViolations();
        $attribute = $this->attributeRepository->getByIdentifier($identifier);
        $normalizedAttribute = $attribute->normalize();
        Assert::assertEquals(AttributeValidationRule::NONE, $normalizedAttribute['validation_rule']);
        Assert::assertEquals(AttributeRegularExpression::EMPTY, $normalizedAttribute['regular_expression']);
    }

    /**
     * @Given /^an asset family with a text area attribute \'([^\']*)\' with no rich text editor$/
     */
    public function anAssetFamilyWithATextareaAttributeWithNoRichTextEditor(string $attributeCode)
    {
        $identifier = AttributeIdentifier::create('dummy_identifier', $attributeCode, md5('fingerprint'));
        $this->attributeIdentifiers['dummy_identifier'][$attributeCode] = $identifier;

        $this->attributeRepository->create(
            TextAttribute::createTextarea(
                $identifier,
                AssetFamilyIdentifier::fromString('dummy_identifier'),
                AttributeCode::fromString($attributeCode),
                LabelCollection::fromArray([]),
                AttributeOrder::fromInteger(0),
                AttributeIsRequired::fromBoolean(false),
                AttributeIsReadOnly::fromBoolean(false),
                AttributeValuePerChannel::fromBoolean(false),
                AttributeValuePerLocale::fromBoolean(false),
                AttributeMaxLength::fromInteger(150),
                AttributeIsRichTextEditor::fromBoolean(false)
            )
        );
    }

    /**
     * @When /^the user changes the is_rich_text_editor flag of \'([^\']*)\' to \'([^\']*)\'$/
     */
    public function theUserChangesTheIsRichTextEditorFlagOfTo(string $attributeCode, string $newIsRichTextEditor)
    {
        $identifier = $this->attributeIdentifiers['dummy_identifier'][$attributeCode];

        $updateIsRichTextEditor = [
            'identifier'          => (string)$identifier,
            'is_rich_text_editor' => json_decode($newIsRichTextEditor),
        ];
        $this->updateAttribute($updateIsRichTextEditor);
    }

    /**
     * @Then /^the \'([^\']*)\' attribute should have a text editor$/
     */
    public function theAttributeShouldHaveATextEditor(string $attributeCode)
    {
        $identifier = $this->attributeIdentifiers['dummy_identifier'][$attributeCode];

        $this->constraintViolationsContext->assertThereIsNoViolations();
        $attribute = $this->attributeRepository->getByIdentifier($identifier);
        $normalizedAttribute = $attribute->normalize();
        Assert::assertTrue($normalizedAttribute['is_textarea'], 'isTextarea should be true');
        Assert::assertTrue($normalizedAttribute['is_rich_text_editor'], 'IsRichTextEditor should be true');
    }

    /**
     * @When /^the user changes the is_textarea flag and the is_rich_text_editor of \'([^\']*)\' to \'([^\']*)\'$/
     */
    public function theUserChangesTheIsTextareaFlagAndTheIsRichTextEditorOfTo(string $attributeCode, string $newflag)
    {
        $identifier = $this->attributeIdentifiers['dummy_identifier'][$attributeCode];

        $newflag = json_decode($newflag);
        $updates = [
            'identifier'          => (string)$identifier,
            'is_rich_text_editor' => $newflag,
            'is_textarea'         => $newflag,
        ];
        $this->updateAttribute($updates);
    }

    /**
     * @When /^the user changes the text area flag to \'([^\']*)\' and the validation rule of \'([^\']*)\' to \'([^\']*)\'$/
     */
    public function theUserChangesTheTextareaFlagToAndTheValidationRuleOfTo(
        string $textareaFlag,
        string $attributeCode,
        string $validationRule
    ) {
        $identifier = $this->attributeIdentifiers['dummy_identifier'][$attributeCode];

        $updates = [
            'identifier'      => (string)$identifier,
            'is_textarea'     => json_decode($textareaFlag),
            'validation_rule' => $validationRule,
        ];
        $this->updateAttribute($updates);
    }

    /**
     * @Then /^the attribute \'([^\']*)\' should have a text editor$/
     */
    public function theAttributeShouldHaveATextEditor1(string $attributeCode)
    {
        $identifier = $this->attributeIdentifiers['dummy_identifier'][$attributeCode];

        $this->constraintViolationsContext->assertThereIsNoViolations();
        $attribute = $this->attributeRepository->getByIdentifier($identifier);
        $normalizedAttribute = $attribute->normalize();
        Assert::assertTrue(
            $normalizedAttribute['is_rich_text_editor'],
            'Expected is rich text editor to be true, but found false'
        );
    }

    private function updateAttribute(array $updates): void
    {
        $editAttribute = $this->editAttributeCommandFactory->create($updates);
        $this->constraintViolationsContext->addViolations($this->validator->validate($editAttribute));
        if (!$this->constraintViolationsContext->hasViolations()) {
            ($this->handler)($editAttribute);
        }
    }

    /**
     * @Given /^an asset family with an attribute \'([^\']*)\' having a single value for all locales$/
     */
    public function anAssetFamilyWithAnAttributeNotHavingOneValuePerLocale(string $attributeCode)
    {
        $identifier = AttributeIdentifier::create('dummy_identifier', $attributeCode, md5('fingerprint'));
        $this->attributeIdentifiers['dummy_identifier'][$attributeCode] = $identifier;

        $this->attributeRepository->create(
            TextAttribute::createText(
                $identifier,
                AssetFamilyIdentifier::fromString('dummy_identifier'),
                AttributeCode::fromString($attributeCode),
                LabelCollection::fromArray([]),
                AttributeOrder::fromInteger(0),
                AttributeIsRequired::fromBoolean(true),
                AttributeIsReadOnly::fromBoolean(false),
                AttributeValuePerChannel::fromBoolean(false),
                AttributeValuePerLocale::fromBoolean(false),
                AttributeMaxLength::fromInteger(100),
                AttributeValidationRule::none(),
                AttributeRegularExpression::createEmpty()
            )
        );
    }

    /**
     * @When /^the user updates the value_per_locale of \'([^\']*)\' to \'([^\']*)\'$/
     */
    public function theUserUpdatesTheValue_per_localeOfTo(string $attributeCode, string $valuePerLocale): void
    {
        $identifier = $this->attributeIdentifiers['dummy_identifier'][$attributeCode];

        $valuePerLocale = json_decode($valuePerLocale);
        $updateValuePerLocale = [
            'identifier'       => (string)$identifier,
            'value_per_locale' => $valuePerLocale,
        ];
        $this->updateAttribute($updateValuePerLocale);
    }

    /**
     * @Then /^the value_per_locale of \'([^\']*)\' should be \'([^\']*)\'$/
     */
    public function theValue_per_localeOfShouldBe(string $attributeCode, string $valuePerLocale)
    {
        $identifier = $this->attributeIdentifiers['dummy_identifier'][$attributeCode];

        $attribute = $this->attributeRepository->getByIdentifier($identifier);
        Assert::assertEquals(json_decode($valuePerLocale), $attribute->normalize()['value_per_locale']);
    }

    /**
     * @Given /^an asset family with an attribute \'([^\']*)\' having a single value for all channels$/
     */
    public function anAssetFamilyWithAnAttributeNotHavingOneValuePerChannel(string $attributeCode)
    {
        $identifier = AttributeIdentifier::create('dummy_identifier', $attributeCode, md5('fingerprint'));
        $this->attributeIdentifiers['dummy_identifier'][$attributeCode] = $identifier;

        $this->attributeRepository->create(
            TextAttribute::createText(
                $identifier,
                AssetFamilyIdentifier::fromString('dummy_identifier'),
                AttributeCode::fromString($attributeCode),
                LabelCollection::fromArray([]),
                AttributeOrder::fromInteger(0),
                AttributeIsRequired::fromBoolean(true),
                AttributeIsReadOnly::fromBoolean(false),
                AttributeValuePerChannel::fromBoolean(false),
                AttributeValuePerLocale::fromBoolean(false),
                AttributeMaxLength::fromInteger(100),
                AttributeValidationRule::none(),
                AttributeRegularExpression::createEmpty()
            )
        );
    }

    /**
     * @When /^the user updates the value_per_channel of \'([^\']*)\' to \'([^\']*)\'$/
     */
    public function theUserUpdatesTheValue_per_channelOfTo(string $attributeCode, string $valuePerChannel)
    {
        $identifier = $this->attributeIdentifiers['dummy_identifier'][$attributeCode];

        $valuePerChannel = json_decode($valuePerChannel);
        $updateValuePerChannel = [
            'identifier'       => (string)$identifier,
            'value_per_locale' => $valuePerChannel,
        ];
        $this->updateAttribute($updateValuePerChannel);
    }

    /**
     * @Given /^an asset family with an option attribute with no available options$/
     */
    public function aAssetFamilyWithAnOptionAttributeWithNoAvailableOptions()
    {
        $identifier = AttributeIdentifier::create('designer', 'favorite_color', md5('fingerprint'));
        $this->attributeIdentifiers['dummy_identifier']['favorite_color'] = $identifier;

        $this->attributeRepository->create(
            OptionAttribute::create(
                $identifier,
                AssetFamilyIdentifier::fromString('dummy_identifier'),
                AttributeCode::fromString('favorite_color'),
                LabelCollection::fromArray([]),
                AttributeOrder::fromInteger(0),
                AttributeIsRequired::fromBoolean(true),
                AttributeIsReadOnly::fromBoolean(false),
                AttributeValuePerChannel::fromBoolean(true),
                AttributeValuePerLocale::fromBoolean(true)
            )
        );
    }

    /**
     * @Given /^an asset family with an option attribute with some options$/
     */
    public function aAssetFamilyWithAnOptionAttributeWithSomeOptions()
    {
        $this->activatedLocales->save(LocaleIdentifier::fromCode('en_US'));
        $this->activatedLocales->save(LocaleIdentifier::fromCode('fr_FR'));

        $identifier = AttributeIdentifier::create('designer', 'favorite_color', md5('fingerprint'));
        $this->attributeIdentifiers['dummy_identifier']['favorite_color'] = $identifier;

        $optionAttribute = OptionAttribute::create(
            $identifier,
            AssetFamilyIdentifier::fromString('dummy_identifier'),
            AttributeCode::fromString('favorite_color'),
            LabelCollection::fromArray([]),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true)
        );
        $optionAttribute->setOptions(
            [
                AttributeOption::create(OptionCode::fromString('red'), LabelCollection::fromArray(['en_US' => 'Red'])),
                AttributeOption::create(
                    OptionCode::fromString('green'),
                    LabelCollection::fromArray(['en_US' => 'Green'])
                )
            ]
        );
        $this->attributeRepository->create($optionAttribute);
    }

    /**
     * @When /^the user adds the option \'([^\']*)\' with label \'([^\']*)\' for locale \'([^\']*)\' to this attribute$/
     */
    public function theUserAddsTheOptionWithLabelForLocaleToThisAttribute(
        string $optionCode,
        string $label,
        string $locale
    ): void {
        if (isset($this->attributeIdentifiers['dummy_identifier']['favorite_color'])) {
            $identifier = $this->attributeIdentifiers['dummy_identifier']['favorite_color'];
        } else {
            $identifier = 'unknown';
        }

        $this->updateAttribute(
            [
                'identifier' => (string)$identifier,
                'options'    => [
                    [
                        'code'   => $optionCode,
                        'labels' => [$locale => $label]
                    ]
                ]
            ]
        );
    }

    /**
     * @Then /^the option( collection)? attribute should have an option \'([^\']*)\' with label \'([^\']*)\' for the locale \'([^\']*)\'$/
     */
    public function theOptionAttributeShouldHaveAnOptionWithLabelForTheLocale(
        $isCollection,
        $optionCode,
        $label,
        $locale
    ) {
        $identifier = $this->attributeIdentifiers['dummy_identifier']['favorite_color'];

        $this->constraintViolationsContext->assertThereIsNoViolations();
        $attribute = $this->attributeRepository->getByIdentifier($identifier);
        Assert::assertNotEmpty($attribute->normalize()['options']);
        Assert::assertEquals(
            [
                [
                    'code'   => $optionCode,
                    'labels' => [$locale => $label],
                ],
            ],
            $attribute->normalize()['options']
        );
    }

    /**
     * @Given /^the option attribute has (\d+) option$/
     */
    public function theOptionAttributeHasOption(int $optionsCount)
    {
        $identifier = $this->attributeIdentifiers['dummy_identifier']['favorite_color'];

        $this->constraintViolationsContext->assertThereIsNoViolations();
        $attribute = $this->attributeRepository->getByIdentifier($identifier);
        Assert::assertCount($optionsCount, $attribute->normalize()['options']);
    }


    /**
     * @Given /^an asset family with an option attribute \'([^\']+)\' and the label \'([^\']+)\' equal to \'([^\']+)\'$/
     */
    public function aAssetFamilyWithAnOptionAttributeAndTheLabelEqualTo($attributeCode, $locale, $label)
    {
        $identifier = AttributeIdentifier::create('designer', $attributeCode, md5('fingerprint'));
        $this->attributeIdentifiers['dummy_identifier'][$attributeCode] = $identifier;

        $optionAttribute = OptionAttribute::create(
            $identifier,
            AssetFamilyIdentifier::fromString('dummy_identifier'),
            AttributeCode::fromString($attributeCode),
            LabelCollection::fromArray(
                [
                    $locale => $label
                ]
            ),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true)
        );
        $this->attributeRepository->create($optionAttribute);
    }

    /**
     * @When /^the user adds (\d+) options to this attribute$/
     */
    public function theUserAddsOptionsToThisAttribute(int $optionsCount)
    {
        if (isset($this->attributeIdentifiers['dummy_identifier']['favorite_color'])) {
            $identifier = $this->attributeIdentifiers['dummy_identifier']['favorite_color'];
        } else {
            $identifier = 'unknown';
        }

        $tooManyOptions = [];
        for ($i = 0; $i < $optionsCount; $i++) {
            $tooManyOptions[] = [
                'code'   => (string)$i,
                'labels' => []
            ];
        }

        $this->updateAttribute(
            [
                'identifier' => (string)$identifier,
                'options'    => $tooManyOptions
            ]
        );
    }

    /**
     * @When /^the user adds the \'([^\']*)\' option twice$/
     */
    public function theUserAddsTheSameOptionTwice(string $duplicateOptionCode)
    {
        if (isset($this->attributeIdentifiers['dummy_identifier']['favorite_color'])) {
            $identifier = $this->attributeIdentifiers['dummy_identifier']['favorite_color'];
        } else {
            $identifier = 'unknown';
        }

        $duplicates = [
            [
                'code'   => $duplicateOptionCode,
                'labels' => [],
            ],
            [
                'code'   => $duplicateOptionCode,
                'labels' => [],
            ],
        ];

        $this->updateAttribute(
            [
                'identifier' => (string)$identifier,
                'options'    => $duplicates
            ]
        );
    }

    /**
     * @When /^the user sets the \'([^\']*)\' option$/
     */
    public function theUserSetsTheOption($optionCode)
    {
        if (isset($this->attributeIdentifiers['dummy_identifier']['favorite_color'])) {
            $identifier = $this->attributeIdentifiers['dummy_identifier']['favorite_color'];
        } else {
            $identifier = 'unknown';
        }

        $this->updateAttribute(
            [
                'identifier' => (string)$identifier,
                'options'    => [
                    [
                        'code'   => json_decode($optionCode, true),
                        'labels' => [],
                    ],
                ]
            ]
        );
    }

    /**
     * @When /^the user sets an option with a code too long$/
     */
    public function theUserSetsAnOptionWithACodeTooLong()
    {
        if (isset($this->attributeIdentifiers['dummy_identifier']['favorite_color'])) {
            $identifier = $this->attributeIdentifiers['dummy_identifier']['favorite_color'];
        } else {
            $identifier = 'unknown';
        }

        $this->updateAttribute(
            [
                'identifier' => (string)$identifier,
                'options'    => [
                    [
                        'code'   => str_repeat('a', 256),
                        'labels' => [],
                    ],
                ]
            ]
        );
    }

    /**
     * @When /^the user sets an option with a label \'([^\']*)\'$/
     */
    public function theUserSetsAnOptionWithALabel($invalidLabel)
    {
        if (isset($this->attributeIdentifiers['dummy_identifier']['favorite_color'])) {
            $identifier = $this->attributeIdentifiers['dummy_identifier']['favorite_color'];
        } else {
            $identifier = 'unknown';
        }

        $this->updateAttribute(
            [
                'identifier' => (string)$identifier,
                'options'    => [
                    [
                        'code'   => 'option_code',
                        'labels' => ['fr_FR' => json_decode($invalidLabel, true)],
                    ],
                ]
            ]
        );
    }

    /**
     * @Given /^an asset family with an option collection attribute with some options$/
     */
    public function aAssetFamilyWithAnOptionCollectionAttributeWithSomeOptions()
    {
        $this->activatedLocales->save(LocaleIdentifier::fromCode('en_US'));
        $this->activatedLocales->save(LocaleIdentifier::fromCode('fr_FR'));

        $identifier = AttributeIdentifier::create('designer', 'favorite_color', md5('fingerprint'));
        $this->attributeIdentifiers['dummy_identifier']['favorite_color'] = $identifier;

        $optionAttribute = OptionCollectionAttribute::create(
            $identifier,
            AssetFamilyIdentifier::fromString('dummy_identifier'),
            AttributeCode::fromString('favorite_color'),
            LabelCollection::fromArray([]),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true)
        );
        $optionAttribute->setOptions(
            [
                AttributeOption::create(OptionCode::fromString('red'), LabelCollection::fromArray(['en_US' => 'Red'])),
                AttributeOption::create(
                    OptionCode::fromString('green'),
                    LabelCollection::fromArray(['en_US' => 'Green'])
                )
            ]
        );
        $this->attributeRepository->create($optionAttribute);
    }

    /**
     * @Given /^an asset family with a number attribute \'([^\']*)\' and the label \'([^\']*)\' equal to \'([^\']*)\'$/
     */
    public function aAssetFamilyWithANumberAttributeAndTheLabelEqualTo(
        string $attributeCode,
        string $localeCode,
        string $label
    ): void {
        $this->activatedLocales->save(LocaleIdentifier::fromCode($localeCode));

        $identifier = AttributeIdentifier::create('dummy_identifier', $attributeCode, md5('fingerprint'));
        $this->attributeIdentifiers['dummy_identifier'][$attributeCode] = $identifier;

        $this->attributeRepository->create(
            NumberAttribute::create(
                $identifier,
                AssetFamilyIdentifier::fromString('dummy_identifier'),
                AttributeCode::fromString($attributeCode),
                LabelCollection::fromArray([$localeCode => $label]),
                AttributeOrder::fromInteger(0),
                AttributeIsRequired::fromBoolean(true),
                AttributeIsReadOnly::fromBoolean(false),
                AttributeValuePerChannel::fromBoolean(true),
                AttributeValuePerLocale::fromBoolean(true),
                AttributeDecimalsAllowed::fromBoolean(false),
                AttributeLimit::limitless(),
                AttributeLimit::limitless()
            )
        );
    }

    /**
     * @Given /^an asset family with a number attribute \'([^\']*)\' non decimal$/
     * @Given /^an asset family with a number attribute \'([^\']*)\' no min value$/
     * @Given /^an asset family with a number attribute \'([^\']*)\' no max value$/
     */
    public function aAssetFamilyWithANumberAttributeNonDecimal(string $attributeCode): void
    {
        $identifier = AttributeIdentifier::create('dummy_identifier', $attributeCode, md5('fingerprint'));
        $this->attributeIdentifiers['dummy_identifier'][$attributeCode] = $identifier;

        $this->attributeRepository->create(
            NumberAttribute::create(
                $identifier,
                AssetFamilyIdentifier::fromString('dummy_identifier'),
                AttributeCode::fromString($attributeCode),
                LabelCollection::fromArray([]),
                AttributeOrder::fromInteger(0),
                AttributeIsRequired::fromBoolean(false),
                AttributeIsReadOnly::fromBoolean(false),
                AttributeValuePerChannel::fromBoolean(false),
                AttributeValuePerLocale::fromBoolean(false),
                AttributeDecimalsAllowed::fromBoolean(false),
                AttributeLimit::limitless(),
                AttributeLimit::limitless()
            )
        );
    }

    /**
     * @When /^the user sets the \'([^\']*)\' attribute to have decimal values$/
     */
    public function theUserSetsTheAttributeToHaveDecimalValues(string $attributeCode): void
    {
        $identifier = $this->attributeIdentifiers['dummy_identifier'][$attributeCode];

        $updateDecimalsAllowed = [
            'identifier' => (string)$identifier,
            'decimals_allowed' => true,
        ];
        $this->updateAttribute($updateDecimalsAllowed);
    }

    /**
     * @Then /^\'([^\']*)\' could have decimal values$/
     */
    public function couldHaveDecimalValues(string $attributeCode): void
    {
        $identifier = $this->attributeIdentifiers['dummy_identifier'][$attributeCode];

        $this->constraintViolationsContext->assertThereIsNoViolations();
        $attribute = $this->attributeRepository->getByIdentifier($identifier);
        Assert::assertEquals(true, $attribute->normalize()['decimals_allowed']);
    }

    /**
     * @When /^the user sets the min value of \'([^\']*)\' to (\d+)$/
     */
    public function theUserSetsTheMinValueOfTo(string $attributeCode, string $minValue)
    {
        $identifier = $this->attributeIdentifiers['dummy_identifier'][$attributeCode];

        $updateMinValue = ['identifier' => (string) $identifier, 'min_value' => $minValue];
        $this->updateAttribute($updateMinValue);
    }

    /**
     * @Then /^\'([^\']*)\' min value should be (\d+)$/
     */
    public function minValueShouldBe(string $attributeCode, string $expectedMinValue)
    {
        $identifier = $this->attributeIdentifiers['dummy_identifier'][$attributeCode];

        $this->constraintViolationsContext->assertThereIsNoViolations();
        $attribute = $this->attributeRepository->getByIdentifier($identifier);
        Assert::assertEquals($expectedMinValue, $attribute->normalize()['min_value']);
    }

    /**
     * @Given /^an asset family with a number attribute \'([^\']*)\' with a min value$/
     * @Given /^an asset family with a number attribute \'([^\']*)\' with a min value set to 150$/
     */
    public function aAssetFamilyWithANumberAttributeWithAMinValue(string $attributeCode)
    {
        $identifier = AttributeIdentifier::create('dummy_identifier', $attributeCode, md5('fingerprint'));
        $this->attributeIdentifiers['dummy_identifier'][$attributeCode] = $identifier;

        $this->attributeRepository->create(
            NumberAttribute::create(
                $identifier,
                AssetFamilyIdentifier::fromString('dummy_identifier'),
                AttributeCode::fromString($attributeCode),
                LabelCollection::fromArray([]),
                AttributeOrder::fromInteger(0),
                AttributeIsRequired::fromBoolean(false),
                AttributeIsReadOnly::fromBoolean(false),
                AttributeValuePerChannel::fromBoolean(false),
                AttributeValuePerLocale::fromBoolean(false),
                AttributeDecimalsAllowed::fromBoolean(false),
                AttributeLimit::fromString('150'),
                AttributeLimit::limitless()
            )
        );
    }

    /**
     * @When /^the user unsets the min value of \'([^\']*)\'$/
     */
    public function theUserUnsetsTheMinValueOf(string $attributeCode)
    {
        $identifier = $this->attributeIdentifiers['dummy_identifier'][$attributeCode];

        $updateMinValue = ['identifier' => (string) $identifier, 'min_value' => null];
        $this->updateAttribute($updateMinValue);
    }

    /**
     * @Then /^\'([^\']*)\' should not have a min value$/
     */
    public function shouldNotHaveAMinValue(string $attributeCode)
    {
        $identifier = $this->attributeIdentifiers['dummy_identifier'][$attributeCode];

        $this->constraintViolationsContext->assertThereIsNoViolations();
        $attribute = $this->attributeRepository->getByIdentifier($identifier);
        Assert::assertNull($attribute->normalize()['min_value']);
    }

    /**
     * @When /^the user sets the max value of \'([^\']*)\' to (\d+)$/
     */
    public function theUserSetsTheMaxValueOfTo(string $attributeCode, string $maxValue)
    {
        $identifier = $this->attributeIdentifiers['dummy_identifier'][$attributeCode];

        $updateMaxValue = ['identifier' => (string) $identifier, 'max_value' => $maxValue];
        $this->updateAttribute($updateMaxValue);
    }

    /**
     * @Then /^\'([^\']*)\' max value should be (\d+)$/
     */
    public function maxValueShouldBe(string $attributeCode, string $expectedMaxValue)
    {
        $identifier = $this->attributeIdentifiers['dummy_identifier'][$attributeCode];

        $this->constraintViolationsContext->assertThereIsNoViolations();
        $attribute = $this->attributeRepository->getByIdentifier($identifier);
        Assert::assertEquals($expectedMaxValue, $attribute->normalize()['max_value']);
    }

    /**
     * @Given /^an asset family with a number attribute \'([^\']*)\'$/
     * @Given /^an asset family with a number attribute \'([^\']*)\' with a max value$/
     * @Given /^an asset family with a number attribute \'([^\']*)\' with a max value set to 200$/
     */
    public function aAssetFamilyWithANumberAttributeWithAMaxValue(string $attributeCode)
    {
        $identifier = AttributeIdentifier::create('dummy_identifier', $attributeCode, md5('fingerprint'));
        $this->attributeIdentifiers['dummy_identifier'][$attributeCode] = $identifier;

        $this->attributeRepository->create(
            NumberAttribute::create(
                $identifier,
                AssetFamilyIdentifier::fromString('dummy_identifier'),
                AttributeCode::fromString($attributeCode),
                LabelCollection::fromArray([]),
                AttributeOrder::fromInteger(0),
                AttributeIsRequired::fromBoolean(false),
                AttributeIsReadOnly::fromBoolean(false),
                AttributeValuePerChannel::fromBoolean(false),
                AttributeValuePerLocale::fromBoolean(false),
                AttributeDecimalsAllowed::fromBoolean(false),
                AttributeLimit::limitless(),
                AttributeLimit::fromString('200')
            )
        );
    }

    /**
     * @When /^the user unsets the max value of \'([^\']*)\'$/
     */
    public function theUserUnsetsTheMaxValueOf(string $attributeCode)
    {
        $identifier = $this->attributeIdentifiers['dummy_identifier'][$attributeCode];

        $updateMaxValue = ['identifier' => (string) $identifier, 'max_value' => null];
        $this->updateAttribute($updateMaxValue);
    }

    /**
     * @Then /^\'([^\']*)\' should not have a max value$/
     */
    public function shouldNotHaveAMaxValue(string $attributeCode)
    {
        $identifier = $this->attributeIdentifiers['dummy_identifier'][$attributeCode];

        $this->constraintViolationsContext->assertThereIsNoViolations();
        $attribute = $this->attributeRepository->getByIdentifier($identifier);
        Assert::assertNull($attribute->normalize()['max_value']);
    }

    /**
     * @When /^the user sets the is decimal property of the \'([^\']*)\' attribute to \'([^\']*)\'$/
     */
    public function theUserSetsTheAttributeTo(string $attributeCode, $invalidValue)
    {
        $identifier = $this->attributeIdentifiers['dummy_identifier'][$attributeCode];
        $updateDecimalsAllowed = [
            'identifier'  => (string)$identifier,
            'decimals_allowed' => json_decode($invalidValue),
        ];
        $this->updateAttribute($updateDecimalsAllowed);
    }

    /**
     * @When /^the user sets the min value of the \'([^\']*)\' attribute to \'([^\']*)\'$/
     */
    public function theUserSetsTheMinValueOfTheAttributeTo(string $attributeCode, $invalidValue)
    {
        $identifier = $this->attributeIdentifiers['dummy_identifier'][$attributeCode];
        $updateMinValue = [
            'identifier' => (string)$identifier,
            'min_value'  => json_decode($invalidValue),
        ];
        $this->updateAttribute($updateMinValue);
    }

    /**
     * @When /^the user sets the max value of the \'([^\']*)\' attribute to \'([^\']*)\'$/
     */
    public function theUserSetsTheMaxValueOfTheAttributeTo(string $attributeCode, $invalidValue)
    {
        $identifier = $this->attributeIdentifiers['dummy_identifier'][$attributeCode];
        $updateMinValue = [
            'identifier' => (string)$identifier,
            'max_value'  => json_decode($invalidValue),
        ];
        $this->updateAttribute($updateMinValue);
    }

    /**
     * @When /^the user sets the min value of \'([^\']*)\' to (\d+) and the max value to (\d+)$/
     */
    public function theUserSetsTheMinValueOfToAndTheMaxValueTo(string $attributeCode, string $minValue, string $maxValue)
    {
        $identifier = $this->attributeIdentifiers['dummy_identifier'][$attributeCode];

        $updateMaxValue = [
            'identifier' => (string) $identifier,
            'min_value' => $minValue,
            'max_value' => $maxValue
        ];
        $this->updateAttribute($updateMaxValue);
    }

    /**
     * @Given /^an asset family with an mediaLink attribute \'([^\']*)\' and the label \'([^\']*)\' equal to \'([^\']*)\'$/
     */
    public function aAssetFamilyWithAnMediaLinkAttributeAndTheLabelEqualTo(
        string $attributeCode,
        string $localeCode,
        string $label
    ): void {
        $this->activatedLocales->save(LocaleIdentifier::fromCode($localeCode));

        $identifier = AttributeIdentifier::create('dummy_identifier', $attributeCode, md5('fingerprint'));
        $this->attributeIdentifiers['dummy_identifier'][$attributeCode] = $identifier;

        $this->attributeRepository->create(
            MediaLinkAttribute::create(
                $identifier,
                AssetFamilyIdentifier::fromString('dummy_identifier'),
                AttributeCode::fromString($attributeCode),
                LabelCollection::fromArray([$localeCode => $label]),
                AttributeOrder::fromInteger(0),
                AttributeIsRequired::fromBoolean(true),
                AttributeIsReadOnly::fromBoolean(false),
                AttributeValuePerChannel::fromBoolean(true),
                AttributeValuePerLocale::fromBoolean(true),
                Prefix::fromString(null),
                Suffix::fromString(null),
                MediaLinkMediaType::fromString('image')
            )
        );
    }

    /**
     * @Given /^an asset family with an mediaLink attribute \'([^\']*)\'$/
     */
    public function aAssetFamilyWithAnMediaLinkAttribute(string $attributeCode): void
    {
        $identifier = AttributeIdentifier::create('dummy_identifier', $attributeCode, md5('fingerprint'));
        $this->attributeIdentifiers['dummy_identifier'][$attributeCode] = $identifier;

        $this->attributeRepository->create(
            MediaLinkAttribute::create(
                $identifier,
                AssetFamilyIdentifier::fromString('dummy_identifier'),
                AttributeCode::fromString($attributeCode),
                LabelCollection::fromArray([]),
                AttributeOrder::fromInteger(0),
                AttributeIsRequired::fromBoolean(false),
                AttributeIsReadOnly::fromBoolean(false),
                AttributeValuePerChannel::fromBoolean(false),
                AttributeValuePerLocale::fromBoolean(false),
                Prefix::fromString(null),
                Suffix::fromString(null),
                MediaLinkMediaType::fromString('image')
            )
        );
    }

    /**
     * @When /^the user sets the prefix value of \'([^\']*)\' to \'([^\']*)\'$/
     */
    public function theUserSetsThePrefixValueOfTo(string $attributeCode, string $prefix): void
    {
        $identifier = $this->attributeIdentifiers['dummy_identifier'][$attributeCode];
        $prefix = json_decode($prefix);

        $updatePrefix = [
            'identifier' => (string)$identifier,
            'prefix' => $prefix,
        ];
        $this->updateAttribute($updatePrefix);
    }

    /**
     * @Then /^\'([^\']*)\' prefix should be \'([^\']*)\'$/
     */
    public function prefixShouldBe(string $attributeCode, string $expectedPrefix): void
    {
        $this->constraintViolationsContext->assertThereIsNoViolations();
        $identifier = $this->attributeIdentifiers['dummy_identifier'][$attributeCode];
        $expectedPrefix = json_decode($expectedPrefix);

        $attribute = $this->attributeRepository->getByIdentifier($identifier);
        Assert::assertEquals($expectedPrefix, $attribute->normalize()['prefix']);
    }

    /**
     * @When /^the user sets the suffix value of \'([^\']*)\' to \'([^\']*)\'$/
     */
    public function theUserSetsTheSuffixValueOfTo(string $attributeCode, string $suffix): void
    {
        $identifier = $this->attributeIdentifiers['dummy_identifier'][$attributeCode];
        $suffix = json_decode($suffix);

        $updateSuffix = [
            'identifier' => (string)$identifier,
            'suffix' => $suffix,
        ];
        $this->updateAttribute($updateSuffix);
    }

    /**
     * @Then /^\'([^\']*)\' suffix should be \'([^\']*)\'$/
     */
    public function suffixShouldBe(string $attributeCode, string $expectedSuffix): void
    {
        $this->constraintViolationsContext->assertThereIsNoViolations();
        $identifier = $this->attributeIdentifiers['dummy_identifier'][$attributeCode];
        $expectedSuffix = json_decode($expectedSuffix);

        $attribute = $this->attributeRepository->getByIdentifier($identifier);
        Assert::assertEquals($expectedSuffix, $attribute->normalize()['suffix']);
    }

    /**
     * @When /^the user sets the media type value of \'([^\']*)\' to \'([^\']*)\'$/
     */
    public function theUserSetsTheMediaTypeValueOfTo(string $attributeCode, string $mediaType): void
    {
        $identifier = $this->attributeIdentifiers['dummy_identifier'][$attributeCode];
        $mediaType = json_decode($mediaType);

        $updateMediaType = [
            'identifier' => (string)$identifier,
            'media_type' => $mediaType,
            'type'       => MediaLinkAttribute::ATTRIBUTE_TYPE
        ];
        $this->updateAttribute($updateMediaType);
    }

    /**
     * @Then /^\'([^\']*)\' media type should be \'([^\']*)\'$/
     */
    public function mediaTypeShouldBe(string $attributeCode, string $expectedMediaType): void
    {
        $this->constraintViolationsContext->assertThereIsNoViolations();
        $identifier = $this->attributeIdentifiers['dummy_identifier'][$attributeCode];
        $expectedMediaType = json_decode($expectedMediaType);

        $attribute = $this->attributeRepository->getByIdentifier($identifier);
        Assert::assertEquals($expectedMediaType, $attribute->normalize()['media_type']);
    }

    /**
     * @Given /^an asset family with a media file attribute image with media type image$/
     */
    public function anAssetFamilyWithAMediaFileAttributeImageWithMediaTypeImage()
    {
        $identifier = AttributeIdentifier::create('dummy_identifier', 'image', md5('fingerprint'));
        $this->attributeIdentifiers['dummy_identifier']['image'] = $identifier;

        $this->attributeRepository->create(
            MediaFileAttribute::create(
                $identifier,
                AssetFamilyIdentifier::fromString('dummy_identifier'),
                AttributeCode::fromString('image'),
                LabelCollection::fromArray([]),
                AttributeOrder::fromInteger(0),
                AttributeIsRequired::fromBoolean(true),
                AttributeIsReadOnly::fromBoolean(false),
                AttributeValuePerChannel::fromBoolean(true),
                AttributeValuePerLocale::fromBoolean(true),
                AttributeMaxFileSize::fromString('200'),
                AttributeAllowedExtensions::fromList(AttributeAllowedExtensions::ALL_ALLOWED),
                MediaFileMediaType::fromString(MediaFileMediaType::IMAGE)
            )
        );
    }

    /**
     * @When /^the user changes the media type to pdf$/
     */
    public function theUserChangesTheMediaTypeToPdf()
    {
        $identifier = $this->attributeIdentifiers['dummy_identifier']['image'];

        $updateMediaType = [
            'identifier' => (string)$identifier,
            'media_type' => MediaFileMediaType::PDF,
            'type'       => MediaFileAttribute::ATTRIBUTE_TYPE
        ];
        $this->updateAttribute($updateMediaType);
    }

    /**
     * @Then /^the media type should be pdf$/
     */
    public function theMediaTypeShouldBePdf()
    {
        $identifier = $this->attributeIdentifiers['dummy_identifier']['image'];

        $this->constraintViolationsContext->assertThereIsNoViolations();
        $attribute = $this->attributeRepository->getByIdentifier($identifier);
        Assert::assertEquals(MediaFileMediaType::PDF, $attribute->normalize()['media_type']);
    }

    /**
     * @When /^the user changes the media type to an unknown media type$/
     */
    public function theUserChangesTheMediaTypeToAnUnknownMediaType()
    {
        $identifier = $this->attributeIdentifiers['dummy_identifier']['image'];

        $updateMediaType = [
            'identifier' => (string)$identifier,
            'media_type' => 'Unknown_And_Invalid',
            'type'       => MediaFileAttribute::ATTRIBUTE_TYPE
        ];
        $this->updateAttribute($updateMediaType);
    }
}
