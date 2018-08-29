<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\tests\back\Acceptance\Context;

use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory\EditAttributeCommandFactoryInterface;
use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\EditAttributeHandler;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeAllowedExtensions;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIsRichTextEditor;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeMaxFileSize;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\ImageAttribute;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\LabelCollection;
use Akeneo\EnrichedEntity\Domain\Repository\AttributeRepositoryInterface;
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
    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var EditAttributeCommandFactoryInterface */
    private $editAttributeCommandFactory;

    /** @var EditAttributeHandler */
    private $handler;

    /** @var ValidatorInterface */
    private $validator;

    /** @var ExceptionContext */
    private $exceptionContext;

    /** @var ConstraintViolationsContext */
    private $constraintViolationsContext;

    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        EditAttributeCommandFactoryInterface $editAttributeCommandFactory,
        EditAttributeHandler $handler,
        ValidatorInterface $validator,
        ConstraintViolationsContext $constraintViolationsContext,
        ExceptionContext $exceptionContext
    ) {
        $this->validator = $validator;
        $this->attributeRepository = $attributeRepository;
        $this->editAttributeCommandFactory = $editAttributeCommandFactory;
        $this->handler = $handler;
        $this->exceptionContext = $exceptionContext;
        $this->constraintViolationsContext = $constraintViolationsContext;
    }

    /**
     * @Given /^the following text attributes:$/
     */
    public function theFollowingTextAttributes(TableNode $attributesTable)
    {
        foreach ($attributesTable->getHash() as $attribute) {
            $this->attributeRepository->create(TextAttribute::createText(
                AttributeIdentifier::create($attribute['entity_identifier'], $attribute['code']),
                EnrichedEntityIdentifier::fromString($attribute['entity_identifier']),
                AttributeCode::fromString($attribute['code']),
                LabelCollection::fromArray(json_decode($attribute['labels'], true)),
                AttributeOrder::fromInteger((int) $attribute['order']),
                AttributeIsRequired::fromBoolean((bool) $attribute['required']),
                AttributeValuePerChannel::fromBoolean((bool) $attribute['value_per_channel']),
                AttributeValuePerLocale::fromBoolean((bool) $attribute['value_per_locale']),
                AttributeMaxLength::fromInteger((int) $attribute['max_length']),
                AttributeValidationRule::none(),
                AttributeRegularExpression::createEmpty()
            ));
        }
    }

    /**
     * @When /^the user deletes the attribute "(.+)" linked to the enriched entity "(.+)"$/
     */
    public function theUserDeletesTheAttribute(string $attributeIdentifier, string $entityIdentifier)
    {
        $identifier = AttributeIdentifier::create($entityIdentifier, $attributeIdentifier);
        $this->attributeRepository->deleteByIdentifier($identifier);
    }

    /**
     * @Given /^an enriched entity with a text attribute \'([^\']*)\' and the label \'([^\']*)\' equal to \'([^\']*)\'$/
     */
    public function anEnrichedEntityWithATextAttributeAndTheLabelEqualTo(
        string $attributeCode,
        string $localeCode,
        string $label
    ) : void {
        $this->attributeRepository->create(TextAttribute::createText(
            AttributeIdentifier::create('dummy_identifier', $attributeCode),
            EnrichedEntityIdentifier::fromString('dummy_identifier'),
            AttributeCode::fromString($attributeCode),
            LabelCollection::fromArray([$localeCode => $label]),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(100),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        ));
    }

    /**
     * @Then /^the label \'([^\']*)\' of the \'([^\']*)\' attribute should be \'([^\']*)\'$/
     */
    public function theLabelOfTheAttributeShouldBe(string $localeCode, string $attributeCode, $expectedLabel): void
    {
        $this->constraintViolationsContext->assertThereIsNoViolations();
        $attribute = $this->attributeRepository->getByIdentifier(AttributeIdentifier::create(
            'dummy_identifier',
            $attributeCode
        ));
        Assert::assertEquals($expectedLabel, $attribute->getLabel($localeCode));
    }

    /**
     * @Given /^an enriched entity with a text attribute \'([^\']*)\' non required$/
     */
    public function anEnrichedEntityWithATextAttributeNonRequired(string $attributeCode)
    {
        $this->attributeRepository->create(TextAttribute::createText(
            AttributeIdentifier::create('dummy_identifier', $attributeCode),
            EnrichedEntityIdentifier::fromString('dummy_identifier'),
            AttributeCode::fromString($attributeCode),
            LabelCollection::fromArray([]),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxLength::fromInteger(100),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        ));
    }

    /**
     * @When /^the user sets the \'([^\']*)\' attribute required$/
     */
    public function theUserSetsTheAttributeRequired(string $attributeCode)
    {
        $this->theUserSetsTheIsRequiredPropertyOfTo($attributeCode, "true");
    }

    /**
     * @Then /^\'([^\']*)\' should be required$/
     */
    public function thenShouldBeRequired(string $attributeCode)
    {
        $this->constraintViolationsContext->assertThereIsNoViolations();
        $attribute = $this->attributeRepository->getByIdentifier(AttributeIdentifier::create(
            'dummy_identifier',
            $attributeCode
        ));
        Assert::assertEquals(true, $attribute->normalize()['is_required']);
    }

    /**
     * @Given /^an enriched entity with a text attribute \'([^\']*)\' and max length (\d+)$/
     */
    public function anEnrichedEntityWithATextAttributeAndMaxLength(string $attributeCode, int $maxLength)
    {
        $this->attributeRepository->create(TextAttribute::createText(
            AttributeIdentifier::create('dummy_identifier', $attributeCode),
            EnrichedEntityIdentifier::fromString('dummy_identifier'),
            AttributeCode::fromString($attributeCode),
            LabelCollection::fromArray([]),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxLength::fromInteger($maxLength),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        ));
    }

    /**
     * @When /^the user changes the max length of \'([^\']*)\' to \'([^\']*)\'$/
     */
    public function theUserChangesTheMaxLengthOfTo(string $attributeCode, string $newMaxLength)
    {
        $updateMaxLength = [
            'identifier' => [
                'identifier'                 => $attributeCode,
                'enriched_entity_identifier' => 'dummy_identifier',
            ],
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
        $attribute = $this->attributeRepository->getByIdentifier(AttributeIdentifier::create(
            'dummy_identifier',
            $attributeCode
        ));
        Assert::assertEquals($expectedMaxLength, $attribute->normalize()['max_length']);
    }

    /**
     * @Given /^an enriched entity with an image attribute \'([^\']*)\' and the label \'([^\']*)\' equal to \'([^\']*)\'$/
     */
    public function anEnrichedEntityWithAImageAttributeAndTheLabelEqualTo(string $attributeCode, string $label, string $localeCode)
    {
        $this->attributeRepository->create(ImageAttribute::create(
            AttributeIdentifier::create('dummy_identifier', $attributeCode),
            EnrichedEntityIdentifier::fromString('dummy_identifier'),
            AttributeCode::fromString($attributeCode),
            LabelCollection::fromArray([$localeCode => $label]),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxFileSize::fromString('210'),
            AttributeAllowedExtensions::fromList(['png'])
        ));
    }

    /**
     * @Given /^an enriched entity with an image attribute \'([^\']*)\' with max file size \'([^\']*)\'$/
     */
    public function anEnrichedEntityWithATextAttributeAndMaxFileSize(string $attributeCode, string $maxFileSize): void
    {
        $this->attributeRepository->create(ImageAttribute::create(
            AttributeIdentifier::create('dummy_identifier', $attributeCode),
            EnrichedEntityIdentifier::fromString('dummy_identifier'),
            AttributeCode::fromString($attributeCode),
            LabelCollection::fromArray([]),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxFileSize::fromString($maxFileSize),
            AttributeAllowedExtensions::fromList(['png'])
        ));
    }

    /**
     * @When /^the user changes the max file size of \'([^\']*)\' to \'([^\']*)\'$/
     */
    public function theUserChangesTheMaxFileSizeOfTo(string $attributeCode, string $newMaxFileSize): void
    {
        $updateMaxFileSize = [
            'identifier'    => [
                'identifier'                 => $attributeCode,
                'enriched_entity_identifier' => 'dummy_identifier',
            ],
            'max_file_size' => json_decode($newMaxFileSize),
        ];
        $this->updateAttribute($updateMaxFileSize);
    }

    /**
     * @Then /^the max file size of \'([^\']*)\' should be \'([^\']*)\'$/
     */
    public function thenTheMaxFileSizeOfShouldBe(string $attributeCode, string $expectedMaxFileSize): void
    {
        $this->constraintViolationsContext->assertThereIsNoViolations();
        $attribute = $this->attributeRepository->getByIdentifier(AttributeIdentifier::create(
            'dummy_identifier',
            $attributeCode
        ));
        Assert::assertEquals($expectedMaxFileSize, $attribute->normalize()['max_file_size']);
    }

    /**
     * @Given /^an enriched entity with a text attribute \'([^\']*)\' and no allowed extensions$/
     */
    public function anEnrichedEntityWithATextAttributeAndNoAllowedExtensions(string $attributeCode)
    {
        $this->anEnrichedEntityWithAnImageAttributeWithAllowedExtensions($attributeCode, '[]');
    }

    /**
     * @When /^the user changes adds \'([^\']*)\' to the allowed extensions of \'([^\']*)\'$/
     */
    public function theUserChangesAddsToTheAllowedExtensionsOf(string $newAllowedExtension, string $attributeCode)
    {
        $updateAllowedExtensions = [
            'identifier'         => [
                'identifier'                 => $attributeCode,
                'enriched_entity_identifier' => 'dummy_identifier',
            ],
            'allowed_extensions' => json_decode($newAllowedExtension),
        ];
        $this->updateAttribute($updateAllowedExtensions);
    }

    /**
     * @Then /^the \'([^\']*)\' should have \'([^\']*)\' as an allowed extension$/
     */
    public function thenShouldHaveAsAnAllowedExtension(string $attributeCode, string $expectedAllowedExtension)
    {
        $this->constraintViolationsContext->assertThereIsNoViolations();
        $attribute = $this->attributeRepository->getByIdentifier(AttributeIdentifier::create(
            'dummy_identifier',
            $attributeCode
        ));
        $expectedAllowedExtension = json_decode($expectedAllowedExtension);
        Assert::assertEquals($expectedAllowedExtension, $attribute->normalize()['allowed_extensions']);
    }

    /**
     * @When /^the user updates the \'([^\']*)\' attribute label with \'([^\']*)\' on the locale \'([^\']*)\'$/
     */
    public function theUserUpdatesTheAttributeLabelWithOnTheLocale1(string $attributeCode, string $label, string $localeCode): void
    {
        $label = json_decode($label);
        $localeCode = json_decode($localeCode);
        $updateLabels = [
            'identifier' => [
                'identifier'                 => $attributeCode,
                'enriched_entity_identifier' => 'dummy_identifier',
            ],
            'labels'     => [$localeCode => $label],
        ];
        $this->updateAttribute($updateLabels);
    }

    /**
     * @When /^the user sets the is_required property of \'([^\']*)\' to \'([^\']*)\'$/
     */
    public function theUserSetsTheIsRequiredPropertyOfTo(string $attributeCode, $invalidValue)
    {
        $updateIsRequired = [
            'identifier'  => [
                'identifier'                 => $attributeCode,
                'enriched_entity_identifier' => 'dummy_identifier',
            ],
            'is_required' => json_decode($invalidValue),
        ];
        $this->updateAttribute($updateIsRequired);
    }

    /**
     * @Then /^there should be no limit for the max length of \'([^\']*)\'$/
     */
    public function thenThereShouldBeNoLimitForTheMaxLengthOf(string $attributeCode)
    {
        $this->constraintViolationsContext->assertThereIsNoViolations();
        $attribute = $this->attributeRepository->getByIdentifier(AttributeIdentifier::create(
            'dummy_identifier',
            $attributeCode
        ));
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
        $this->constraintViolationsContext->assertThereIsNoViolations();
        $attribute = $this->attributeRepository->getByIdentifier(AttributeIdentifier::create(
            'dummy_identifier',
            $attributeCode
        ));
        Assert::assertEquals(null, $attribute->normalize()['max_file_size']);
    }

    /**
     * @Given /^an enriched entity with an image attribute \'([^\']*)\' non required$/
     */
    public function anEnrichedEntityWithAnImageAttributeNonRequired(string $attributeCode)
    {
        $this->attributeRepository->create(ImageAttribute::create(
            AttributeIdentifier::create('dummy_identifier', $attributeCode),
            EnrichedEntityIdentifier::fromString('dummy_identifier'),
            AttributeCode::fromString($attributeCode),
            LabelCollection::fromArray([]),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxFileSize::fromString('200'),
            AttributeAllowedExtensions::fromList(['png'])
        ));
    }

    /**
     * @Given /^an enriched entity with an image attribute \'([^\']*)\' with allowed extensions: \'([^\']*)\'$/
     */
    public function anEnrichedEntityWithAnImageAttributeWithAllowedExtensions(string $attributeCode, string $normalizedExtensions): void
    {
        $extensions = json_decode($normalizedExtensions);
        $this->attributeRepository->create(ImageAttribute::create(
            AttributeIdentifier::create('dummy_identifier', $attributeCode),
            EnrichedEntityIdentifier::fromString('dummy_identifier'),
            AttributeCode::fromString($attributeCode),
            LabelCollection::fromArray([]),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxFileSize::fromString('200'),
            AttributeAllowedExtensions::fromList($extensions)
        ));
    }

    /**
     * @Given /^an enriched entity with a text area attribute \'([^\']*)\'$/
     */
    public function anEnrichedEntityWithATextAreaAttribute(string $attributeCode)
    {
        $this->attributeRepository->create(TextAttribute::createTextArea(
            AttributeIdentifier::create('dummy_identifier', $attributeCode),
            EnrichedEntityIdentifier::fromString('dummy_identifier'),
            AttributeCode::fromString($attributeCode),
            LabelCollection::fromArray([]),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxLength::fromInteger(150),
            AttributeIsRichTextEditor::fromBoolean(true)
        ));
    }

    /**
     * @When /^the user changes the is text area flag of \'([^\']*)\' to \'([^\']*)\'$/
     */
    public function theUserChangesTheIsTextAreaFlagTo(string $attributeCode, string $newIsTextArea)
    {
        $updateIsTextArea = [
            'identifier'                 => [
                'identifier'                 => $attributeCode,
                'enriched_entity_identifier' => 'dummy_identifier',
            ],
            'is_text_area'                => json_decode($newIsTextArea),
        ];
        $this->updateAttribute($updateIsTextArea);
    }

    /**
     * @Then /^the \'([^\']*)\' attribute should be a simple text$/
     */
    public function theAttributeShouldBeASimpleText(string $attributeCode): void
    {
        $this->constraintViolationsContext->assertThereIsNoViolations();
        $attribute = $this->attributeRepository->getByIdentifier(AttributeIdentifier::create('dummy_identifier', $attributeCode));
        $normalizedAttribute = $attribute->normalize();
        Assert::assertFalse($normalizedAttribute['is_text_area'], 'isTextArea should be false');
        Assert::assertFalse($normalizedAttribute['is_rich_text_editor'], 'isRichTextEditor should be false');
    }

    /**
     * @Given /^an enriched entity with a text attribute \'([^\']*)\'$/
     */
    public function anEnrichedEntityWithATextAttribute(string $attributeCode)
    {
        $this->attributeRepository->create(TextAttribute::createText(
            AttributeIdentifier::create('dummy_identifier', $attributeCode),
            EnrichedEntityIdentifier::fromString('dummy_identifier'),
            AttributeCode::fromString($attributeCode),
            LabelCollection::fromArray([]),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxLength::fromInteger(150),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        ));
    }

    /**
     * @Then /^the \'([^\']*)\' attribute should be a text area$/
     */
    public function theAttributeShouldBeATextArea($attributeCode)
    {
        $this->constraintViolationsContext->assertThereIsNoViolations();
        $attribute = $this->attributeRepository->getByIdentifier(AttributeIdentifier::create('dummy_identifier', $attributeCode));
        $normalizedAttribute = $attribute->normalize();
        Assert::assertTrue($normalizedAttribute['is_text_area'], 'isTextArea should be true');
        Assert::assertEquals(AttributeValidationRule::NONE, $normalizedAttribute['validation_rule'], 'validationRule should be none');
        Assert::assertNull($normalizedAttribute['regular_expression'], 'regularExpression should be null');
    }

    /**
     * @Given /^an enriched entity with a text attribute \'([^\']*)\' with no validation rule$/
     */
    public function anEnrichedEntityWithATextAttributeWithNoValidationRule(string $attributeCode)
    {
        $this->attributeRepository->create(TextAttribute::createText(
            AttributeIdentifier::create('dummy_identifier', $attributeCode),
            EnrichedEntityIdentifier::fromString('dummy_identifier'),
            AttributeCode::fromString($attributeCode),
            LabelCollection::fromArray([]),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxLength::fromInteger(150),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        ));
    }

    /**
     * @When /^the user changes the validation rule of \'([^\']*)\' to \'([^\']*)\'$/
     */
    public function theUserChangesTheValidationRuleOfTo(string $attributeCode, string $newValidationRule)
    {
        $updateValidationRule = [
            'identifier'                 => [
                'identifier'                 => $attributeCode,
                'enriched_entity_identifier' => 'dummy_identifier',
            ],
            'validation_rule'            => json_decode($newValidationRule),
        ];
        $this->updateAttribute($updateValidationRule);
    }

    /**
     * @Then /^the validation rule of \'([^\']*)\' should be \'([^\']*)\'$/
     */
    public function theValidationRuleOfShouldBe(string $attributeCode, string $validationRule)
    {
        $this->constraintViolationsContext->assertThereIsNoViolations();
        $attribute = $this->attributeRepository->getByIdentifier(AttributeIdentifier::create('dummy_identifier', $attributeCode));
        $normalizedAttribute = $attribute->normalize();
        Assert::assertEquals($validationRule, $normalizedAttribute['validation_rule']);
    }

    /**
     * @Given /^an enriched entity with a text attribute \'([^\']*)\' with a regular expression \'([^\']*)\'$/
     */
    public function anEnrichedEntityWithATextAttributeWithARegularExpression(string $attributeCode, string $regularExpression)
    {
        $this->attributeRepository->create(TextAttribute::createText(
            AttributeIdentifier::create('dummy_identifier', $attributeCode),
            EnrichedEntityIdentifier::fromString('dummy_identifier'),
            AttributeCode::fromString($attributeCode),
            LabelCollection::fromArray([]),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxLength::fromInteger(150),
            AttributeValidationRule::fromString(AttributeValidationRule::REGULAR_EXPRESSION),
            AttributeRegularExpression::fromString($regularExpression)
        ));
    }

    /**
     * @Then /^the regular expression of \'([^\']*)\' should be empty$/
     */
    public function theRegularExpressionOfShouldBeEmpty(string $attributeCode)
    {
        $this->constraintViolationsContext->assertThereIsNoViolations();
        $attribute = $this->attributeRepository->getByIdentifier(AttributeIdentifier::create('dummy_identifier', $attributeCode));
        $normalizedAttribute = $attribute->normalize();
        Assert::assertFalse($normalizedAttribute['is_text_area'], 'isTextArea should be false');
        Assert::assertNotNull($normalizedAttribute['validation_rule'], 'validationRule should be not be null');
        Assert::assertNull($normalizedAttribute['regular_expression'], 'regularExpression should be null');
    }

    /**
     * @When /^the user changes the regular expression of \'([^\']*)\' to \'([^\']*)\'$/
     */
    public function theUserChangesTheRegularExpressionOfToW09(string $attributeCode, string $newRegularExpression)
    {
        $editRegularExpression = [
            'identifier'                 => [
                'identifier'                 => $attributeCode,
                'enriched_entity_identifier' => 'dummy_identifier',
            ],
            'regular_expression'         => json_decode($newRegularExpression),
        ];
        $this->updateAttribute($editRegularExpression);
    }

    /**
     * @Then /^the regular expression of \'([^\']*)\' should be \'([^\']*)\'$/
     */
    public function theRegularExpressionOfShouldBeW09(string $attributeCode, string $regularExpression)
    {
        $this->constraintViolationsContext->assertThereIsNoViolations();
        $attribute = $this->attributeRepository->getByIdentifier(AttributeIdentifier::create('dummy_identifier', $attributeCode));
        $normalizedAttribute = $attribute->normalize();
        Assert::assertFalse($normalizedAttribute['is_text_area'], 'isTextArea should be false');
        Assert::assertEquals(AttributeValidationRule::REGULAR_EXPRESSION, $normalizedAttribute['validation_rule']);
        Assert::assertEquals($regularExpression, $normalizedAttribute['regular_expression']);
    }

    /**
     * @When /^the user removes the regular expression of \'([^\']*)\'$/
     */
    public function theUserRemovesTheRegularExpressionOf(string $attributeCode)
    {
        $removeRegularExpression = [
            'identifier'                 => [
                'identifier'                 => $attributeCode,
                'enriched_entity_identifier' => 'dummy_identifier',
            ],
            'regular_expression'         => null,
        ];
        $this->updateAttribute($removeRegularExpression);
    }

    /**
     * @Then /^there is no regular expression set on \'([^\']*)\'$/
     */
    public function thereIsNoRegularExpressionSetOn(string $attributeCode)
    {
        $this->constraintViolationsContext->assertThereIsNoViolations();
        $attribute = $this->attributeRepository->getByIdentifier(AttributeIdentifier::create('dummy_identifier', $attributeCode));
        $normalizedAttribute = $attribute->normalize();
        Assert::assertFalse($normalizedAttribute['is_text_area'], 'isTextArea should be false');
        Assert::assertEquals(AttributeRegularExpression::EMPTY, $normalizedAttribute['regular_expression']);
    }

    /**
     * @When /^the user removes the validation rule of \'([^\']*)\'$/
     */
    public function theUserRemovesTheValidationRuleOf(string $attributeCode)
    {
        $removeValidationRule = [
            'identifier'                 => [
                'identifier'                 => $attributeCode,
                'enriched_entity_identifier' => 'dummy_identifier',
            ],
            'validation_rule'            => AttributeValidationRule::NONE,
        ];
        $this->updateAttribute($removeValidationRule);
    }

    /**
     * @Then /^there is no validation rule set on \'([^\']*)\'$/
     */
    public function thereIsNoValidationRuleSetOn(string $attributeCode)
    {
        $this->constraintViolationsContext->assertThereIsNoViolations();
        $attribute = $this->attributeRepository->getByIdentifier(AttributeIdentifier::create('dummy_identifier', $attributeCode));
        $normalizedAttribute = $attribute->normalize();
        Assert::assertEquals(AttributeValidationRule::NONE, $normalizedAttribute['validation_rule']);
        Assert::assertEquals(AttributeRegularExpression::EMPTY, $normalizedAttribute['regular_expression']);
    }

    /**
     * @Given /^an enriched entity with a text area attribute \'([^\']*)\' with no rich text editor$/
     */
    public function anEnrichedEntityWithATextAreaAttributeWithNoRichTextEditor(string $attributeCode)
    {
        $this->attributeRepository->create(TextAttribute::createTextArea(
            AttributeIdentifier::create('dummy_identifier', $attributeCode),
            EnrichedEntityIdentifier::fromString('dummy_identifier'),
            AttributeCode::fromString($attributeCode),
            LabelCollection::fromArray([]),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxLength::fromInteger(150),
            AttributeIsRichTextEditor::fromBoolean(false)
        ));
    }

    /**
     * @When /^the user changes the is_rich_text_editor flag of \'([^\']*)\' to \'([^\']*)\'$/
     */
    public function theUserChangesTheIsRichTextEditorFlagOfTo(string $attributeCode, string $newIsRichTextEditor)
    {
        $updateIsRichTextEditor = [
            'identifier'          => [
                'identifier'                 => $attributeCode,
                'enriched_entity_identifier' => 'dummy_identifier',
            ],
            'is_rich_text_editor' => json_decode($newIsRichTextEditor),
        ];
        $this->updateAttribute($updateIsRichTextEditor);
    }

    /**
     * @Then /^the \'([^\']*)\' attribute should have a text editor$/
     */
    public function theAttributeShouldHaveATextEditor(string $attributeCode)
    {
        $this->constraintViolationsContext->assertThereIsNoViolations();
        $attribute = $this->attributeRepository->getByIdentifier(AttributeIdentifier::create('dummy_identifier', $attributeCode));
        $normalizedAttribute = $attribute->normalize();
        Assert::assertTrue($normalizedAttribute['is_text_area'], 'isTextArea should be true');
        Assert::assertTrue($normalizedAttribute['is_rich_text_editor'], 'IsRichTextEditor should be true');
    }

    /**
     * @When /^the user changes the is_text_area flag and the is_rich_text_editor of \'([^\']*)\' to \'([^\']*)\'$/
     */
    public function theUserChangesTheIsTextAreaFlagAndTheIsRichTextEditorOfTo(string $attributeCode, string $newflag)
    {
        $newflag = json_decode($newflag);
        $updates = [
            'identifier'          => [
                'identifier'                 => $attributeCode,
                'enriched_entity_identifier' => 'dummy_identifier',
            ],
            'is_rich_text_editor' => $newflag,
            'is_text_area'        => $newflag,
        ];
        $this->updateAttribute($updates);
    }

    /**
     * @When /^the user changes the text area flag to \'([^\']*)\' and the validation rule of \'([^\']*)\' to \'([^\']*)\'$/
     */
    public function theUserChangesTheTextAreaFlagToAndTheValidationRuleOfTo(string $textAreaFlag, string $attributeCode, string $validationRule)
    {
        $updates = [
            'identifier'      => [
                'identifier'                 => $attributeCode,
                'enriched_entity_identifier' => 'dummy_identifier',
            ],
            'is_text_area'    => json_decode($textAreaFlag),
            'validation_rule' => $validationRule
        ];
        $this->updateAttribute($updates);
    }

    /**
     * @Then /^the attribute \'([^\']*)\' should have a text editor$/
     */
    public function theAttributeShouldHaveATextEditor1(string $attributeCode)
    {
        $this->constraintViolationsContext->assertThereIsNoViolations();
        $attribute = $this->attributeRepository->getByIdentifier(AttributeIdentifier::create('dummy_identifier', $attributeCode));
        $normalizedAttribute = $attribute->normalize();
        Assert::assertTrue($normalizedAttribute['is_rich_text_editor'], 'Expected is rich text editor to be true, but found false');
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
     * @Given /^an enriched entity with an attribute \'([^\']*)\' having a single value for all locales$/
     */
    public function anEnrichedEntityWithAnAttributeNotHavingOneValuePerLocale(string $attributeCode)
    {
        $this->attributeRepository->create(TextAttribute::createText(
            AttributeIdentifier::create('dummy_identifier', $attributeCode),
            EnrichedEntityIdentifier::fromString('dummy_identifier'),
            AttributeCode::fromString($attributeCode),
            LabelCollection::fromArray([]),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxLength::fromInteger(100),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        ));
    }

    /**
     * @When /^the user updates the value_per_locale of \'([^\']*)\' to \'([^\']*)\'$/
     */
    public function theUserUpdatesTheValue_per_localeOfTo(string $attributeCode, string $valuePerLocale): void
    {
        $valuePerLocale = json_decode($valuePerLocale);
        $updateValuePerLocale = [
            'identifier' => [
                'identifier'                 => $attributeCode,
                'enriched_entity_identifier' => 'dummy_identifier',
            ],
            'value_per_locale'     => $valuePerLocale,
        ];
        $this->updateAttribute($updateValuePerLocale);
    }

    /**
     * @Then /^the value_per_locale of \'([^\']*)\' should be \'([^\']*)\'$/
     */
    public function theValue_per_localeOfShouldBe(string $attributeCode, string $valuePerLocale)
    {
        $attribute = $this->attributeRepository->getByIdentifier(AttributeIdentifier::create(
            'dummy_identifier',
            $attributeCode
        ));
        Assert::assertEquals(json_decode($valuePerLocale), $attribute->normalize()['value_per_locale']);
    }

    /**
     * @Given /^an enriched entity with an attribute \'([^\']*)\' having a single value for all channels$/
     */
    public function anEnrichedEntityWithAnAttributeNotHavingOneValuePerChannel(string $attributeCode)
    {
        $this->attributeRepository->create(TextAttribute::createText(
            AttributeIdentifier::create('dummy_identifier', $attributeCode),
            EnrichedEntityIdentifier::fromString('dummy_identifier'),
            AttributeCode::fromString($attributeCode),
            LabelCollection::fromArray([]),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxLength::fromInteger(100),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        ));
    }

    /**
     * @When /^the user updates the value_per_channel of \'([^\']*)\' to \'([^\']*)\'$/
     */
    public function theUserUpdatesTheValue_per_channelOfTo(string $attributeCode, string $valuePerChannel)
    {
        $valuePerChannel = json_decode($valuePerChannel);
        $updateValuePerChannel = [
            'identifier' => [
                'identifier'                 => $attributeCode,
                'enriched_entity_identifier' => 'dummy_identifier',
            ],
            'value_per_locale'     => $valuePerChannel,
        ];
        $this->updateAttribute($updateValuePerChannel);
    }
}
