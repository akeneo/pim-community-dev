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

namespace Akeneo\AssetManager\Acceptance\Context;

use Akeneo\AssetManager\Application\Attribute\AppendAttributeOption\AppendAttributeOptionCommand;
use Akeneo\AssetManager\Application\Attribute\AppendAttributeOption\AppendAttributeOptionHandler;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsReadOnly;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOption\AttributeOption;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOption\OptionCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\AssetManager\Domain\Model\Attribute\OptionAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\OptionCollectionAttribute;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;
use Behat\Behat\Context\Context;
use PHPUnit\Framework\Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AppendAttributeOptionContext implements Context
{
    private AttributeRepositoryInterface $attributeRepository;

    private AppendAttributeOptionHandler $appendAttributeOptionHandler;

    private ValidatorInterface $validator;

    private ConstraintViolationsContext $constraintViolationsContext;

    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        AppendAttributeOptionHandler $appendAttributeOptionHandler,
        ValidatorInterface $validator,
        ConstraintViolationsContext $constraintViolationsContext
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->appendAttributeOptionHandler = $appendAttributeOptionHandler;
        $this->validator = $validator;
        $this->constraintViolationsContext = $constraintViolationsContext;
    }

    /**
     * @Given /^an option attribute$/
     */
    public function anOptionAttribute()
    {
        $optionAttribute = OptionAttribute::create(
            AttributeIdentifier::fromString('color'),
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString('color'),
            LabelCollection::fromArray([]),
            AttributeOrder::fromInteger(3),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
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
     * @When /^the user appends a new option for this option attribute$/
     */
    public function theUserAppendsANewOptionForThisAttribute()
    {
        $command = new AppendAttributeOptionCommand(
            'designer',
            'color',
            'red',
            ['en_US' => 'Red', 'fr_FR' => 'Rouge']
        );

        $violations = $this->validator->validate($command);
        $this->constraintViolationsContext->addViolations($violations);

        if ($violations->count() === 0) {
            ($this->appendAttributeOptionHandler)($command);
        }
    }

    /**
     * @Then /^the option is added into the option collection of this attribute$/
     */
    public function theOptionIsAddedIntoTheOptionCollectionOfTheAttribute()
    {
        $attribute = $this->attributeRepository->getByIdentifier(AttributeIdentifier::fromString('color'));
        Assert::assertTrue($attribute->hasAttributeOption(OptionCode::fromString('red')));

        $option = $attribute->getAttributeOption(OptionCode::fromString('red'));

        $expectedOption = AttributeOption::create(
            OptionCode::fromString('red'),
            LabelCollection::fromArray(['en_US' => 'Red', 'fr_FR' => 'Rouge'])
        );

        $this->constraintViolationsContext->assertThereIsNoViolations();
        Assert::assertEquals($expectedOption, $option);
    }

    /**
     * @Given an option collection attribute
     */
    public function anOptionCollectionAttribute()
    {
        $optionAttribute = OptionCollectionAttribute::create(
            AttributeIdentifier::fromString('color'),
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString('color'),
            LabelCollection::fromArray([]),
            AttributeOrder::fromInteger(3),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(true)
        );
        $optionAttribute->setOptions([
            AttributeOption::create(
                OptionCode::fromString('blue'),
                LabelCollection::fromArray([])
            ),
        ]);

        $this->attributeRepository->create($optionAttribute);
    }

    /**
     * @When the user appends a new option for this option collection attribute
     */
    public function theUserAppendsANewOptionForThisOptionCollectionAttribute()
    {
        $command = new AppendAttributeOptionCommand(
            'designer',
            'color',
            'red',
            ['en_US' => 'Red', 'fr_FR' => 'Rouge']
        );

        $violations = $this->validator->validate($command);
        $this->constraintViolationsContext->addViolations($violations);

        if ($violations->count() === 0) {
            ($this->appendAttributeOptionHandler)($command);
        }
    }

    /**
     * @Given an option collection attribute with the maximum number of options
     */
    public function anOptionCollectionAttributeWithTheMaximumNumberOfOptions()
    {
        $optionAttribute = OptionAttribute::create(
            AttributeIdentifier::fromString('color'),
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString('color'),
            LabelCollection::fromArray([]),
            AttributeOrder::fromInteger(3),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(true)
        );

        $options = [];
        for ($i = 0; $i < 100; $i++) {
            $options[] = AttributeOption::create(
                OptionCode::fromString(sprintf('code_%s', $i)),
                LabelCollection::fromArray([])
            );
        }
        $optionAttribute->setOptions($options);

        $this->attributeRepository->create($optionAttribute);
    }

    /**
     * @Given /^an option attribute with the maximum number of options$/
     */
    public function anOptionAttributeWithTheMaximumNumberOfOptions()
    {
        $optionAttribute = OptionCollectionAttribute::create(
            AttributeIdentifier::fromString('color'),
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString('color'),
            LabelCollection::fromArray([]),
            AttributeOrder::fromInteger(3),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(true)
        );

        $options = [];
        for ($i = 0; $i < 100; $i++) {
            $options[] = AttributeOption::create(
                OptionCode::fromString(sprintf('code_%s', $i)),
                LabelCollection::fromArray([])
            );
        }
        $optionAttribute->setOptions($options);

        $this->attributeRepository->create($optionAttribute);
    }

    /**
     * @Given /^an option collection attribute having one option with the code Red$/
     */
    public function anOptionCollectionAttributeHavingOneOptionWithTheCodeRed()
    {
        $optionAttribute = OptionCollectionAttribute::create(
            AttributeIdentifier::fromString('color'),
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString('color'),
            LabelCollection::fromArray([]),
            AttributeOrder::fromInteger(3),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(true)
        );
        $optionAttribute->setOptions([
            AttributeOption::create(
                OptionCode::fromString('red'),
                LabelCollection::fromArray([])
            )
        ]);

        $this->attributeRepository->create($optionAttribute);
    }

    /**
     * @Given /^an option collection attribute Color with a Red option$/
     */
    public function anOptionCollectionAttributeColorWithARedOption()
    {
        $optionAttribute = OptionCollectionAttribute::create(
            AttributeIdentifier::fromString('color'),
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString('color'),
            LabelCollection::fromArray([]),
            AttributeOrder::fromInteger(3),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(true)
        );
        $optionAttribute->setOptions([
            AttributeOption::create(
                OptionCode::fromString('red'),
                LabelCollection::fromArray([])
            )
        ]);

        $this->attributeRepository->create($optionAttribute);
    }

    /**
     * @When /^the user appends a Red option into the option collection attribute$/
     */
    public function theUserAppendsARedOptionIntoTheOptionCollectionAttribute()
    {
        $command = new AppendAttributeOptionCommand(
            'designer',
            'color',
            'red',
            ['en_US' => 'Red', 'fr_FR' => 'Rouge']
        );

        $violations = $this->validator->validate($command);
        $this->constraintViolationsContext->addViolations($violations);

        if ($violations->count() === 0) {
            ($this->appendAttributeOptionHandler)($command);
        }
    }
}
