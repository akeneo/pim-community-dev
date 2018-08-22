<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\tests\back\Acceptance\Context;

use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory\EditAllowedExtensionsCommand;
use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory\EditAttributeCommand;
use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory\EditLabelsCommand;
use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory\EditMaxFileSizeCommand;
use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory\EditMaxLengthCommand;
use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory\EditRequiredCommand;
use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\EditAttributeHandler;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeAllowedExtensions;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeMaxFileSize;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeRequired;
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

    /** @var EditAttributeHandler */
    private $handler;

    /** @var ValidatorInterface */
    private $validator;

    /** @var ExceptionContext */
    private $exceptionContext;

    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        EditAttributeHandler $handler,
        ValidatorInterface $validator,
        ExceptionContext $exceptionContext
    ) {
        $this->validator = $validator;
        $this->attributeRepository = $attributeRepository;
        $this->handler = $handler;
        $this->exceptionContext = $exceptionContext;
    }

    /**
     * @Given /^an enriched entity with a text attribute \'([^\']*)\' and the label \'([^\']*)\' equal to \'([^\']*)\'$/
     */
    public function anEnrichedEntityWithATextAttributeAndTheLabelEqualTo(
        string $attributeCode,
        string $localeCode,
        string $label
    ) : void {
        $this->attributeRepository->create(TextAttribute::create(
            AttributeIdentifier::create('dummy_identifier', $attributeCode),
            EnrichedEntityIdentifier::fromString('dummy_identifier'),
            AttributeCode::fromString($attributeCode),
            LabelCollection::fromArray([$localeCode => $label]),
            AttributeOrder::fromInteger(0),
            AttributeRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(100)
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
        $this->attributeRepository->create(TextAttribute::create(
            AttributeIdentifier::create('dummy_identifier', $attributeCode),
            EnrichedEntityIdentifier::fromString('dummy_identifier'),
            AttributeCode::fromString($attributeCode),
            LabelCollection::fromArray([]),
            AttributeOrder::fromInteger(0),
            AttributeRequired::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxLength::fromInteger(100)
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
     * @Then /^then \'([^\']*)\' should be required$/
     */
    public function thenShouldBeRequired(string $attributeCode)
    {
        $attribute = $this->attributeRepository->getByIdentifier(AttributeIdentifier::create(
            'dummy_identifier',
            $attributeCode
        ));
        Assert::assertEquals(true, $attribute->normalize()['required']);
    }

    /**
     * @Given /^an enriched entity with a text attribute \'([^\']*)\' and max length (\d+)$/
     */
    public function anEnrichedEntityWithATextAttributeAndMaxLength(string $attributeCode, int $maxLength)
    {
        $this->attributeRepository->create(TextAttribute::create(
            AttributeIdentifier::create('dummy_identifier', $attributeCode),
            EnrichedEntityIdentifier::fromString('dummy_identifier'),
            AttributeCode::fromString($attributeCode),
            LabelCollection::fromArray([]),
            AttributeOrder::fromInteger(0),
            AttributeRequired::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxLength::fromInteger($maxLength)
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
     * @Then /^then \'([^\']*)\' max length should be (\d+)$/
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
            AttributeRequired::fromBoolean(true),
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
            AttributeRequired::fromBoolean(true),
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
     * @Then /^then the max file size of \'([^\']*)\' should be \'([^\']*)\'$/
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
     * @Then /^then \'([^\']*)\' should have \'([^\']*)\' as an allowed extension$/
     */
    public function thenShouldHaveAsAnAllowedExtension(string $attributeCode, string $expectedAllowedExtension)
    {
        $attribute = $this->attributeRepository->getByIdentifier(AttributeIdentifier::create(
            'dummy_identifier',
            $attributeCode
        ));
        Assert::assertContains($expectedAllowedExtension, $attribute->normalize()['allowed_extensions']);
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
     * @When /^the user sets the is required property of \'([^\']*)\' to \'([^\']*)\'$/
     */
    public function theUserSetsTheIsRequiredPropertyOfTo(string $attributeCode, $invalidValue)
    {
        $invalidValue = json_decode($invalidValue);

        $identifier = ['identifier' => $attributeCode, 'enriched_entity_identifier' => 'dummy_identifier'];
        $editIsRequired = new EditRequiredCommand();
        $editIsRequired->identifier = $identifier;
        $editIsRequired->required = $invalidValue;

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
     * @Then /^then there should be no limit for the max length of \'([^\']*)\'$/
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
     * @Then /^then there should be no limit for the max file size of \'([^\']*)\'$/
     */
    public function thenThereShouldBeNoLimitForTheMaxFileSizeOf(string $attributeCode)
    {
        $attribute = $this->attributeRepository->getByIdentifier(AttributeIdentifier::create(
            'dummy_identifier',
            $attributeCode
        ));
        Assert::assertEquals(AttributeMaxFileSize::NO_LIMIT, $attribute->normalize()['max_file_size']);
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
            AttributeRequired::fromBoolean(false),
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
            AttributeRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxFileSize::fromString('200'),
            AttributeAllowedExtensions::fromList($extensions)
        ));
    }
}
