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

namespace Akeneo\ReferenceEntity\Acceptance\Context;

use Akeneo\ReferenceEntity\Application\Attribute\EditAttributeOption\EditAttributeOptionCommand;
use Akeneo\ReferenceEntity\Application\Attribute\EditAttributeOption\EditAttributeOptionHandler;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOption\AttributeOption;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOption\OptionCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\OptionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\OptionCollectionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Repository\AttributeRepositoryInterface;
use Behat\Behat\Context\Context;
use PHPUnit\Framework\Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class EditAttributeOptionContext implements Context
{
    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var EditAttributeOptionHandler*/
    private $editAttributeOptionHandler;

    /** @var ValidatorInterface */
    private $validator;

    /** @var ConstraintViolationsContext */
    private $constraintViolationsContext;

    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        EditAttributeOptionHandler $editAttributeOptionHandler,
        ValidatorInterface $validator,
        ConstraintViolationsContext $constraintViolationsContext
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->editAttributeOptionHandler = $editAttributeOptionHandler;
        $this->validator = $validator;
        $this->constraintViolationsContext = $constraintViolationsContext;
    }

    /**
     * @Given /^an option attribute with one option$/
     */
    public function anOptionAttributeWithOneOption()
    {
        $optionAttribute = OptionAttribute::create(
            AttributeIdentifier::fromString('color'),
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('color'),
            LabelCollection::fromArray([]),
            AttributeOrder::fromInteger(1),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(true)
        );
        $optionAttribute->setOptions([
            AttributeOption::create(
                OptionCode::fromString('blue'),
                LabelCollection::fromArray(['de_DE' => 'blauw'])
            )
        ]);

        $this->attributeRepository->create($optionAttribute);
    }

    /**
     * @When /^the user edits the option of this attribute$/
     */
    public function theUserEditsTheOptionOfThisOptionAttribute()
    {
        $command = new EditAttributeOptionCommand();
        $command->referenceEntityIdentifier = 'designer';
        $command->attributeCode = 'color';
        $command->optionCode = 'blue';
        $command->labels = ['en_US' => 'Blue', 'fr_FR' => 'Bleu'];

        $violations = $this->validator->validate($command);
        $this->constraintViolationsContext->addViolations($violations);

        if ($violations->count() === 0) {
            ($this->editAttributeOptionHandler)($command);
        }
    }

    /**
     * @Then /^the option is correctly edited$/
     */
    public function theOptionIsCorrectlyEdited()
    {
        $attribute = $this->attributeRepository->getByIdentifier(AttributeIdentifier::fromString('color'));
        Assert::assertTrue($attribute->hasAttributeOption(OptionCode::fromString('blue')));

        $option = $attribute->getAttributeOption(OptionCode::fromString('blue'));

        $expectedOption = AttributeOption::create(
            OptionCode::fromString('blue'),
            LabelCollection::fromArray(['en_US' => 'Blue', 'fr_FR' => 'Bleu'])
        );

        $this->constraintViolationsContext->assertThereIsNoViolations();
        Assert::assertEquals($expectedOption, $option);
    }


    /**
     * @Given /^an option collection attribute with one option$/
     */
    public function anOptionCollectionAttributeWithOneOption()
    {
        $optionAttribute = OptionCollectionAttribute::create(
            AttributeIdentifier::fromString('color'),
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('color'),
            LabelCollection::fromArray([]),
            AttributeOrder::fromInteger(1),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(true)
        );
        $optionAttribute->setOptions([
            AttributeOption::create(
                OptionCode::fromString('blue'),
                LabelCollection::fromArray([])
            )
        ]);

        $this->attributeRepository->create($optionAttribute);
    }

    /**
     * @Given /^an option attribute without option$/
     */
    public function anOptionAttributeWithoutOption()
    {
        $optionAttribute = OptionAttribute::create(
            AttributeIdentifier::fromString('color'),
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('color'),
            LabelCollection::fromArray([]),
            AttributeOrder::fromInteger(1),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(true)
        );

        $this->attributeRepository->create($optionAttribute);
    }
}
