<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\tests\back\Acceptance\Context;

use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory\EditAllowedExtensionsCommand;
use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory\EditAttributeCommand;
use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory\EditAttributeCommandFactoryInterface;
use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory\EditIsRequiredCommand;
use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory\EditIsRichTextEditorCommand;
use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory\EditIsTextAreaCommand;
use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory\EditLabelsCommand;
use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory\EditMaxFileSizeCommand;
use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory\EditMaxLengthCommand;
use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory\EditRegularExpressionCommand;
use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory\EditValidationRuleCommand;
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
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class EditAttributeContext implements Context
{
    /** @var ConstraintViolationList */
    private $violations;

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

    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        EditAttributeCommandFactoryInterface $editAttributeCommandFactory,
        EditAttributeHandler $handler,
        ValidatorInterface $validator,
        ExceptionContext $exceptionContext
    ) {
        $this->validator = $validator;
        $this->attributeRepository = $attributeRepository;
        $this->editAttributeCommandFactory = $editAttributeCommandFactory;
        $this->handler = $handler;
        $this->exceptionContext = $exceptionContext;
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
                AttributeRegularExpression::none()
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
            AttributeRegularExpression::none()
        ));
    }

    /**
     * @Then /^the label \'([^\']*)\' of the \'([^\']*)\' attribute should be \'([^\']*)\'$/
     */
    public function theLabelOfTheAttributeShouldBe(string $localeCode, string $attributeCode, $expectedLabel): void
    {
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
            AttributeRegularExpression::none()
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
            AttributeRegularExpression::none()
        ));
    }

    /**
     * @When /^the user changes the max length of \'([^\']*)\' to \'([^\']*)\'$/
     */
    public function theUserChangesTheMaxLengthOfTo(string $attributeCode, string $newMaxLength)
    {
        $newMaxLength = json_decode($newMaxLength);

        $identifier = ['identifier' => $attributeCode, 'enriched_entity_identifier' => 'dummy_identifier'];
        $editMaxLength = new EditMaxLengthCommand();
        $editMaxLength->identifier = $identifier;
        $editMaxLength->maxLength = $newMaxLength;

        $editAttribute = new EditAttributeCommand();
        $editAttribute->identifier = $identifier;
        $editAttribute->editCommands[] = $editMaxLength;

        $this->executeCommand($editAttribute);
    }

    /**
     * @Then /^\'([^\']*)\' max length should be (\d+)$/
     */
    public function thenMaxLengthShouldBe(string $attributeCode, int $expectedMaxLength)
    {
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
        $newMaxFileSize = json_decode($newMaxFileSize);

        $identifier = ['identifier' => $attributeCode, 'enriched_entity_identifier' => 'dummy_identifier'];
        $editMaxFileSize = new EditMaxFileSizeCommand();
        $editMaxFileSize->identifier = $identifier;
        $editMaxFileSize->maxFileSize = $newMaxFileSize;

        $editAttribute = new EditAttributeCommand();
        $editAttribute->identifier = $identifier;
        $editAttribute->editCommands[] = $editMaxFileSize;

        $this->executeCommand($editAttribute);
    }

    /**
     * @Then /^the max file size of \'([^\']*)\' should be \'([^\']*)\'$/
     */
    public function thenTheMaxFileSizeOfShouldBe(string $attributeCode, string $expectedMaxFileSize): void
    {
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
        $newAllowedExtension = json_decode($newAllowedExtension);

        $identifier = ['identifier' => $attributeCode, 'enriched_entity_identifier' => 'dummy_identifier'];
        $editAllowedExtensions = new EditAllowedExtensionsCommand();
        $editAllowedExtensions->identifier = $identifier;
        $editAllowedExtensions->allowedExtensions = $newAllowedExtension;

        $editAttribute = new EditAttributeCommand();
        $editAttribute->identifier = $identifier;
        $editAttribute->editCommands[] = $editAllowedExtensions;

        $this->executeCommand($editAttribute);
    }

    /**
     * @Then /^the \'([^\']*)\' should have \'([^\']*)\' as an allowed extension$/
     */
    public function thenShouldHaveAsAnAllowedExtension(string $attributeCode, string $expectedAllowedExtension)
    {
        $expectedAllowedExtension = json_decode($expectedAllowedExtension);
        $attribute = $this->attributeRepository->getByIdentifier(AttributeIdentifier::create(
            'dummy_identifier',
            $attributeCode
        ));
        Assert::assertEquals($expectedAllowedExtension, $attribute->normalize()['allowed_extensions']);
    }

    /**
     * @Then /^there should be a validation error on the property \'([^\']*)\' with message \'([^\']*)\'$/
     */
    public function thereShouldBeAValidationErrorOnThePropertyWithMessage(string $expectedPropertyPath, string $message)
    {
        Assert::assertGreaterThan(0, $this->violations->count(), 'There was some violations expected but none were found.');
        $violation = $this->violations->get(0);
        Assert::assertSame($expectedPropertyPath, $violation->getPropertyPath());
        Assert::assertSame($message, $violation->getMessage());
    }

    /**
     * @When /^the user updates the \'([^\']*)\' attribute label with \'([^\']*)\' of type \'([^\']*)\' on the locale \'([^\']*)\'$/
     */
    public function theUserUpdatesTheAttributeLabelWithOfTypeOnTheLocale(string $attributeCode, $label, string $type, string $localeCode, string $localeType): void
    {
        if ('null' === $type) {
            $label = null;
        } elseif ('string' === $type) {
            $label = (string) $label;
        } elseif ('integer' === $type) {
            $label = (int) $label;
        }
        $identifier = ['identifier' => $attributeCode, 'enriched_entity_identifier' => 'dummy_identifier'];
        $editLabel = new EditLabelsCommand();
        $editLabel->identifier = $identifier;
        $editLabel->labels = [$localeCode => $label];

        $editAttribute = new EditAttributeCommand();
        $editAttribute->identifier = $identifier;
        $editAttribute->editCommands[] = $editLabel;

        $this->executeCommand($editAttribute);
    }

    /**
     * @When /^the user updates the \'([^\']*)\' attribute label with \'([^\']*)\' on the locale \'([^\']*)\'$/
     */
    public function theUserUpdatesTheAttributeLabelWithOnTheLocale1(string $attributeCode, string $label, string $localeCode): void
    {
        $label = json_decode($label);
        $localeCode = json_decode($localeCode);

        $identifier = ['identifier' => $attributeCode, 'enriched_entity_identifier' => 'dummy_identifier'];
        $editLabel = new EditLabelsCommand();
        $editLabel->identifier = $identifier;
        $editLabel->labels = [$localeCode => $label];

        $editAttribute = new EditAttributeCommand();
        $editAttribute->identifier = $identifier;
        $editAttribute->editCommands[] = $editLabel;

        $this->executeCommand($editAttribute);
    }

    /**
     * @When /^the user sets the is_required property of \'([^\']*)\' to \'([^\']*)\'$/
     */
    public function theUserSetsTheIsRequiredPropertyOfTo(string $attributeCode, $invalidValue)
    {
        $invalidValue = json_decode($invalidValue);

        $identifier = ['identifier' => $attributeCode, 'enriched_entity_identifier' => 'dummy_identifier'];
        $editIsRequired = new EditIsRequiredCommand();
        $editIsRequired->identifier = $identifier;
        $editIsRequired->isRequired = $invalidValue;

        $editAttribute = new EditAttributeCommand();
        $editAttribute->identifier = $identifier;
        $editAttribute->editCommands[] = $editIsRequired;

        $this->executeCommand($editAttribute);
    }

    private function executeCommand(EditAttributeCommand $editAttribute): void
    {
        $this->violations = $this->validator->validate($editAttribute);
        if (0 === $this->violations->count()) {
            ($this->handler)($editAttribute);
        }
    }

    /**
     * @Then /^there should be no limit for the max length of \'([^\']*)\'$/
     */
    public function thenThereShouldBeNoLimitForTheMaxLengthOf(string $attributeCode)
    {
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
        $newIsTextArea = json_decode($newIsTextArea);

        $identifier = ['identifier' => $attributeCode, 'enriched_entity_identifier' => 'dummy_identifier'];
        $editIsTextArea = new EditIsTextAreaCommand();
        $editIsTextArea->identifier = $identifier;
        $editIsTextArea->isTextArea = $newIsTextArea;

        $editAttribute = new EditAttributeCommand();
        $editAttribute->identifier = $identifier;
        $editAttribute->editCommands[] = $editIsTextArea;

        $this->executeCommand($editAttribute);
    }

    /**
     * @Then /^the \'([^\']*)\' attribute should be a simple text$/
     */
    public function theAttributeShouldBeASimpleText(string $attributeCode): void
    {
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
            AttributeRegularExpression::none()
        ));
    }

    /**
     * @Then /^the \'([^\']*)\' attribute should be a text area$/
     */
    public function theAttributeShouldBeATextArea($attributeCode)
    {
        $attribute = $this->attributeRepository->getByIdentifier(AttributeIdentifier::create('dummy_identifier', $attributeCode));
        $normalizedAttribute = $attribute->normalize();
        Assert::assertTrue($normalizedAttribute['is_text_area'], 'isTextArea should be true');
        Assert::assertNull($normalizedAttribute['validation_rule'], 'validationRule should be null');
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
            AttributeRegularExpression::none()
        ));
    }

    /**
     * @When /^the user changes the validation rule of \'([^\']*)\' to \'([^\']*)\'$/
     */
    public function theUserChangesTheValidationRuleOfTo(string $attributeCode, string $newValidationRule)
    {
        $newValidationRule = json_decode($newValidationRule);

        $identifier = ['identifier' => $attributeCode, 'enriched_entity_identifier' => 'dummy_identifier'];
        $editValidationRule = new EditValidationRuleCommand();
        $editValidationRule->identifier = $identifier;
        $editValidationRule->validationRule = $newValidationRule;

        $editAttribute = new EditAttributeCommand();
        $editAttribute->identifier = $identifier;
        $editAttribute->editCommands[] = $editValidationRule;

        $this->executeCommand($editAttribute);
    }

    /**
     * @Then /^the validation rule of \'([^\']*)\' should be \'([^\']*)\'$/
     */
    public function theValidationRuleOfShouldBe(string $attributeCode, string $validationRule)
    {
        Assert::assertEquals(0, $this->violations->count(), 'There should be no violations, but there was some found');
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
        $newRegularExpression = json_decode($newRegularExpression);

        $identifier = ['identifier' => $attributeCode, 'enriched_entity_identifier' => 'dummy_identifier'];
        $editRegularExpression = new EditRegularExpressionCommand();
        $editRegularExpression->identifier = $identifier;
        $editRegularExpression->regularExpression = $newRegularExpression;

        $editAttribute = new EditAttributeCommand();
        $editAttribute->identifier = $identifier;
        $editAttribute->editCommands[] = $editRegularExpression;

        $this->executeCommand($editAttribute);
    }

    /**
     * @Then /^the regular expression of \'([^\']*)\' should be \'([^\']*)\'$/
     */
    public function theRegularExpressionOfShouldBeW09(string $attributeCode, string $regularExpression)
    {
        Assert::assertEquals(0, $this->violations->count(), 'There should be no violations, but there was some found');
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
        $identifier = ['identifier' => $attributeCode, 'enriched_entity_identifier' => 'dummy_identifier'];
        $editRegularExpression = new EditRegularExpressionCommand();
        $editRegularExpression->identifier = $identifier;
        $editRegularExpression->regularExpression = null;

        $editAttribute = new EditAttributeCommand();
        $editAttribute->identifier = $identifier;
        $editAttribute->editCommands[] = $editRegularExpression;

        $this->executeCommand($editAttribute);
    }

    /**
     * @Then /^there is no regular expression set on \'([^\']*)\'$/
     */
    public function thereIsNoRegularExpressionSetOn(string $attributeCode)
    {
        $attribute = $this->attributeRepository->getByIdentifier(AttributeIdentifier::create('dummy_identifier', $attributeCode));
        $normalizedAttribute = $attribute->normalize();
        Assert::assertFalse($normalizedAttribute['is_text_area'], 'isTextArea should be false');
        Assert::assertEquals(AttributeRegularExpression::NONE, $normalizedAttribute['regular_expression']);
    }

    /**
     * @When /^the user removes the validation rule of \'([^\']*)\'$/
     */
    public function theUserRemovesTheValidationRuleOf(string $attributeCode)
    {
        $identifier = ['identifier' => $attributeCode, 'enriched_entity_identifier' => 'dummy_identifier'];
        $editValidationRule = new EditValidationRuleCommand();
        $editValidationRule->identifier = $identifier;
        $editValidationRule->validationRule = null;

        $editAttribute = new EditAttributeCommand();
        $editAttribute->identifier = $identifier;
        $editAttribute->editCommands[] = $editValidationRule;

        $this->executeCommand($editAttribute);
    }

    /**
     * @Then /^there is no validation rule set on \'([^\']*)\'$/
     */
    public function thereIsNoValidationRuleSetOn(string $attributeCode)
    {
        $attribute = $this->attributeRepository->getByIdentifier(AttributeIdentifier::create('dummy_identifier', $attributeCode));
        $normalizedAttribute = $attribute->normalize();
        Assert::assertEquals(AttributeValidationRule::NONE, $normalizedAttribute['validation_rule']);
        Assert::assertEquals(AttributeRegularExpression::NONE, $normalizedAttribute['regular_expression']);
    }

    /**
     * @Then /^there should be a validation error with message \'([^\']*)\'$/
     */
    public function thereShouldBeAValidationErrorWithMessage(string $message)
    {
        Assert::assertGreaterThan(0, $this->violations->count(), 'There was some violations expected but none were found.');
        $violation = $this->violations->get(0);
        Assert::assertSame($message, $violation->getMessage());
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
        $newIsRichTextEditor = json_decode($newIsRichTextEditor);

        $identifier = ['identifier' => $attributeCode, 'enriched_entity_identifier' => 'dummy_identifier'];
        $editIsTextArea = new EditIsRichTextEditorCommand();
        $editIsTextArea->identifier = $identifier;
        $editIsTextArea->isRichTextEditor = $newIsRichTextEditor;

        $editAttribute = new EditAttributeCommand();
        $editAttribute->identifier = $identifier;
        $editAttribute->editCommands[] = $editIsTextArea;

        $this->executeCommand($editAttribute);
    }

    /**
     * @Then /^the \'([^\']*)\' attribute should have a text editor$/
     */
    public function theAttributeShouldHaveATextEditor(string $attributeCode)
    {
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
        $editAttribute = $this->editAttributeCommandFactory->create($updates);
        $this->executeCommand($editAttribute);
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
        $editAttribute = $this->editAttributeCommandFactory->create($updates);
        $this->executeCommand($editAttribute);
    }

    /**
     * @Then /^the attribute \'([^\']*)\' should have a text editor$/
     */
    public function theAttributeShouldHaveATextEditor1(string $attributeCode)
    {
        Assert::assertEquals(0, $this->violations->count(), 'There should be no violations, but there was some found');
        $attribute = $this->attributeRepository->getByIdentifier(AttributeIdentifier::create('dummy_identifier', $attributeCode));
        $normalizedAttribute = $attribute->normalize();
        Assert::assertTrue($normalizedAttribute['is_rich_text_editor'], 'Expected is rich text editor to be true, but found false');
    }
}
