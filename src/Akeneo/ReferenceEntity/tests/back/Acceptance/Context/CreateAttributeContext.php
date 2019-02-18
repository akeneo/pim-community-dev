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

use Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute\CommandFactory\CreateAttributeCommandFactoryRegistryInterface;
use Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute\CreateAttributeHandler;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Repository\AttributeNotFoundException;
use Akeneo\ReferenceEntity\Domain\Repository\AttributeRepositoryInterface;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use PHPUnit\Framework\Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class CreateAttributeContext implements Context
{
    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var CreateAttributeCommandFactoryRegistryInterface */
    private $commandFactoryRegistry;

    /** @var ValidatorInterface */
    private $validator;

    /** @var CreateAttributeHandler */
    private $handler;

    /** @var ExceptionContext */
    private $exceptionContext;

    /** @var ConstraintViolationsContext */
    private $constraintViolationsContext;

    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        CreateAttributeCommandFactoryRegistryInterface $commandFactoryRegistry,
        ValidatorInterface $validator,
        ConstraintViolationsContext $constraintViolationsContext,
        CreateAttributeHandler $handler,
        ExceptionContext $exceptionContext
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->handler = $handler;
        $this->exceptionContext = $exceptionContext;
        $this->commandFactoryRegistry = $commandFactoryRegistry;
        $this->validator = $validator;
        $this->constraintViolationsContext = $constraintViolationsContext;
    }

    /**
     * @When /^the user creates a text attribute "([^"]*)" linked to the reference entity "([^"]*)" with:$/
     */
    public function theUserCreatesATextAttributeLinkedToTheReferenceEntityWith(string $attributeCode, string $referenceEntityIdentifier, TableNode $attributeData): void
    {
        $attributeData = current($attributeData->getHash());

        $attributeData['type'] = 'text';
        $attributeData['identifier']['identifier'] = $attributeCode;
        $attributeData['identifier']['reference_entity_identifier'] = $referenceEntityIdentifier;
        $attributeData['reference_entity_identifier'] = $referenceEntityIdentifier;
        $attributeData['code'] = $attributeCode;
        $attributeData['order'] = (int) $attributeData['order'];
        $attributeData['is_required'] = json_decode($attributeData['is_required']);
        $attributeData['value_per_channel'] = json_decode($attributeData['value_per_channel']);
        $attributeData['value_per_locale'] = json_decode($attributeData['value_per_locale']);
        $attributeData['labels'] = json_decode($attributeData['labels'], true);
        $attributeData['max_length'] = (int) $attributeData['max_length'];
        $attributeData['is_textarea'] = key_exists('is_textarea', $attributeData) ? json_decode($attributeData['is_textarea']) : null;
        $attributeData['is_rich_text_editor'] = key_exists('is_rich_text_editor', $attributeData) ? json_decode($attributeData['is_rich_text_editor']) : null;
        $attributeData['regular_expression'] = key_exists('regular_expression', $attributeData) ? json_decode($attributeData['regular_expression']) : null;

        $command = $this->commandFactoryRegistry->getFactory($attributeData)->create($attributeData);
        $this->constraintViolationsContext->addViolations($this->validator->validate($command));

        try {
            ($this->handler)($command);
        } catch (\Exception $e) {
            $this->exceptionContext->setException($e);
        }
    }

    /**
     * @Then /^there is a text attribute "([^"]*)" in the reference entity "([^"]*)" with:$/
     */
    public function thereIsAnTextAttributeInTheReferenceEntityWith(
        string $attributeCode,
        string $referenceEntityIdentifier,
        TableNode $attributeData
    ) {
        $attributeIdentifier = $this->attributeRepository->nextIdentifier(
            ReferenceEntityIdentifier::fromString($referenceEntityIdentifier),
            AttributeCode::fromString($attributeCode)
        );

        $expected = current($attributeData->getHash());
        $expected['identifier'] = (string) $attributeIdentifier;
        $expected['reference_entity_identifier'] = $referenceEntityIdentifier;
        $expected['labels'] = json_decode($expected['labels'], true);
        $expected['order'] = (int) $expected['order'];
        $expected['is_required'] = json_decode($expected['is_required']);
        $expected['value_per_channel'] = json_decode($expected['value_per_channel']);
        $expected['value_per_locale'] = json_decode($expected['value_per_locale']);
        $expected['max_length'] = $expected['max_length'] !== 'null' ? (int) $expected['max_length'] : null;
        $expected['is_textarea'] = '' === $expected['is_textarea'] ? null : json_decode($expected['is_textarea']);
        $expected['is_rich_text_editor'] = '' === $expected['is_rich_text_editor'] ? null : json_decode($expected['is_rich_text_editor']);
        $expected['validation_rule'] = '' === $expected['validation_rule'] ? null : $expected['validation_rule'];
        $expected['regular_expression'] = '' === $expected['regular_expression'] ? null : $expected['regular_expression'];
        ksort($expected);

        $attribute = $this->attributeRepository->getByIdentifier($attributeIdentifier);
        $actual = $attribute->normalize();
        ksort($actual);

        Assert::assertSame($expected, $actual);
    }

    /**
     * @When /^the user creates a record attribute "([^"]*)" linked to the reference entity "([^"]*)" with:$/
     */
    public function theUserCreatesARecordAttributeLinkedToTheReferenceEntityWith(string $attributeCode, string $referenceEntityIdentifier, TableNode $attributeData): void
    {
        $attributeData = current($attributeData->getHash());

        $attributeData['type'] = 'record';
        $attributeData['identifier']['identifier'] = $attributeCode;
        $attributeData['identifier']['reference_entity_identifier'] = $referenceEntityIdentifier;
        $attributeData['reference_entity_identifier'] = $referenceEntityIdentifier;
        $attributeData['code'] = $attributeCode;
        $attributeData['order'] = (int) $attributeData['order'];
        $attributeData['is_required'] = json_decode($attributeData['is_required']);
        $attributeData['value_per_channel'] = json_decode($attributeData['value_per_channel']);
        $attributeData['value_per_locale'] = json_decode($attributeData['value_per_locale']);
        $attributeData['labels'] = json_decode($attributeData['labels'], true);

        $command = $this->commandFactoryRegistry->getFactory($attributeData)->create($attributeData);
        $this->constraintViolationsContext->addViolations($this->validator->validate($command));

        try {
            ($this->handler)($command);
        } catch (\Exception $e) {
            $this->exceptionContext->setException($e);
        }
    }

    /**
     * @Then /^there is a record attribute "([^"]*)" in the reference entity "([^"]*)" with:$/
     */
    public function thereIsARecordAttributeInTheReferenceEntityWith(
        string $attributeCode,
        string $referenceEntityIdentifier,
        TableNode $attributeData
    ) {
        $attributeIdentifier = $this->attributeRepository->nextIdentifier(
            ReferenceEntityIdentifier::fromString($referenceEntityIdentifier),
            AttributeCode::fromString($attributeCode)
        );

        $expected = current($attributeData->getHash());
        $expected['identifier'] = (string) $attributeIdentifier;
        $expected['reference_entity_identifier'] = $referenceEntityIdentifier;
        $expected['labels'] = json_decode($expected['labels'], true);
        $expected['order'] = (int) $expected['order'];
        $expected['is_required'] = json_decode($expected['is_required']);
        $expected['value_per_channel'] = json_decode($expected['value_per_channel']);
        $expected['value_per_locale'] = json_decode($expected['value_per_locale']);
        $expected['record_type'] = (string) $expected['record_type'];
        ksort($expected);

        $attribute = $this->attributeRepository->getByIdentifier($attributeIdentifier);
        $actual = $attribute->normalize();
        ksort($actual);

        Assert::assertSame($expected, $actual);
    }

    /**
     * @When /^the user creates a record collection attribute "([^"]*)" linked to the reference entity "([^"]*)" with:$/
     */
    public function theUserCreatesARecordCollectionAttributeLinkedToTheReferenceEntityWith(string $attributeCode, string $referenceEntityIdentifier, TableNode $attributeData): void
    {
        $attributeData = current($attributeData->getHash());

        $attributeData['type'] = 'record_collection';
        $attributeData['identifier']['identifier'] = $attributeCode;
        $attributeData['identifier']['reference_entity_identifier'] = $referenceEntityIdentifier;
        $attributeData['reference_entity_identifier'] = $referenceEntityIdentifier;
        $attributeData['code'] = $attributeCode;
        $attributeData['order'] = (int) $attributeData['order'];
        $attributeData['is_required'] = json_decode($attributeData['is_required']);
        $attributeData['value_per_channel'] = json_decode($attributeData['value_per_channel']);
        $attributeData['value_per_locale'] = json_decode($attributeData['value_per_locale']);
        $attributeData['labels'] = json_decode($attributeData['labels'], true);

        $command = $this->commandFactoryRegistry->getFactory($attributeData)->create($attributeData);
        $violations = $this->validator->validate($command);
        $this->constraintViolationsContext->addViolations($violations);

        try {
            ($this->handler)($command);
        } catch (\Exception $e) {
            $this->exceptionContext->setException($e);
        }
    }

    /**
     * @Then /^there is a record collection attribute "([^"]*)" in the reference entity "([^"]*)" with:$/
     */
    public function thereIsARecordCollectionAttributeInTheReferenceEntityWith(
        string $attributeCode,
        string $referenceEntityIdentifier,
        TableNode $attributeData
    ) {
        $attributeIdentifier = $this->attributeRepository->nextIdentifier(
            ReferenceEntityIdentifier::fromString($referenceEntityIdentifier),
            AttributeCode::fromString($attributeCode)
        );

        $expected = current($attributeData->getHash());
        $expected['identifier'] = (string) $attributeIdentifier;
        $expected['reference_entity_identifier'] = $referenceEntityIdentifier;
        $expected['labels'] = json_decode($expected['labels'], true);
        $expected['order'] = (int) $expected['order'];
        $expected['is_required'] = json_decode($expected['is_required']);
        $expected['value_per_channel'] = json_decode($expected['value_per_channel']);
        $expected['value_per_locale'] = json_decode($expected['value_per_locale']);
        $expected['record_type'] = (string) $expected['record_type'];
        ksort($expected);

        $attribute = $this->attributeRepository->getByIdentifier($attributeIdentifier);
        $actual = $attribute->normalize();
        ksort($actual);

        Assert::assertSame($expected, $actual);
    }

    /**
     * @Then /^there is no attribute "([^"]*)" for the reference entity "([^"]*)"$/
     */
    public function thereIsNoAttributeInTheReferenceEntity(
        string $attributeCode,
        string $referenceEntityIdentifier
    ) {
        $attribute = null;
        $attributeIdentifier = $this->attributeRepository->nextIdentifier(
            ReferenceEntityIdentifier::fromString($referenceEntityIdentifier),
            AttributeCode::fromString($attributeCode)
        );

        try {
            $attribute = $this->attributeRepository->getByIdentifier($attributeIdentifier);
            Assert::assertTrue(false);
        } catch (AttributeNotFoundException $e) {
            Assert::assertNull($attribute);
        }
    }

    /**
     * @When /^the user creates an image attribute "([^"]*)" linked to the reference entity "([^"]*)" with:$/
     */
    public function theUserCreatesAnImageAttributeLinkedToTheReferenceEntityWith(
        $attributeCode,
        $referenceEntityIdentifier,
        TableNode $attributeData
    ) {
        $attributeData = current($attributeData->getHash());

        $attributeData['type'] = 'image';
        $attributeData['code'] = $attributeCode;
        $attributeData['reference_entity_identifier'] = $referenceEntityIdentifier;
        $attributeData['order'] = (int) $attributeData['order'];
        $attributeData['is_required'] = json_decode($attributeData['is_required']);
        $attributeData['value_per_channel'] = json_decode($attributeData['value_per_channel']);
        $attributeData['value_per_locale'] = json_decode($attributeData['value_per_locale']);
        $attributeData['labels'] = json_decode($attributeData['labels'], true);
        $attributeData['allowed_extensions'] = json_decode($attributeData['allowed_extensions']);

        $command = $this->commandFactoryRegistry->getFactory($attributeData)->create($attributeData);
        $this->constraintViolationsContext->addViolations($this->validator->validate($command));

        try {
            ($this->handler)($command);
        } catch (\Exception $e) {
            $this->exceptionContext->setException($e);
        }
    }

    /**
     * @Then /^there is an image attribute "([^"]*)" in the reference entity "([^"]*)" with:$/
     */
    public function thereIsAnAttributeWith(
        string $attributeCode,
        string $referenceEntityIdentifier,
        TableNode $attributeData
    ) {
        $attributeIdentifier = $this->attributeRepository->nextIdentifier(
            ReferenceEntityIdentifier::fromString($referenceEntityIdentifier),
            AttributeCode::fromString($attributeCode)
        );

        $expected = current($attributeData->getHash());
        $expected['identifier'] = (string) $attributeIdentifier;
        $expected['code'] = $attributeCode;
        $expected['reference_entity_identifier'] = $referenceEntityIdentifier;
        $expected['labels'] = json_decode($expected['labels'], true);
        $expected['order'] = (int) $expected['order'];
        $expected['is_required'] = json_decode($expected['is_required']);
        $expected['value_per_channel'] = json_decode($expected['value_per_channel']);
        $expected['value_per_locale'] = json_decode($expected['value_per_locale']);
        $expected['allowed_extensions'] = json_decode($expected['allowed_extensions']);
        $expected['max_file_size'] = $expected['max_file_size'] !== 'null' ? $expected['max_file_size'] : null;
        ksort($expected);

        $attribute = $this->attributeRepository->getByIdentifier($attributeIdentifier);
        $actual = $attribute->normalize();
        ksort($actual);

        Assert::assertSame($expected, $actual);
    }

    /**
     * @Given /^(\d+) random attributes for a reference entity$/
     */
    public function randomAttributesForReferenceEntity(int $number)
    {
        for ($i = 2; $i < $number; $i++) {
            $attributeCode = uniqid();
            $attributeData['type'] = 'text';
            $attributeData['identifier']['identifier'] = $attributeCode;
            $attributeData['identifier']['reference_entity_identifier'] = 'designer';
            $attributeData['reference_entity_identifier'] = 'designer';
            $attributeData['code'] = $attributeCode;
            $attributeData['order'] = $i;
            $attributeData['is_required'] = false;
            $attributeData['value_per_channel'] = false;
            $attributeData['value_per_locale'] = false;
            $attributeData['labels'] = [];
            $attributeData['max_length'] = 50;
            $attributeData['is_textarea'] = null;
            $attributeData['is_rich_text_editor'] = null;
            $attributeData['regular_expression'] = null;

            $command = $this->commandFactoryRegistry->getFactory($attributeData)->create($attributeData);
            $this->constraintViolationsContext->addViolations($this->validator->validate($command));

            ($this->handler)($command);
        }
    }

    /**
     * @When /^the user creates an option attribute "([^"]*)"$/
     */
    public function theUserCreatesAnOptionAttribute(string $attributeCode, TableNode $attributeData): void
    {
        $attributeData = current($attributeData->getHash());

        $attributeData['type'] = 'option';
        $attributeData['identifier']['identifier'] = $attributeCode;
        $attributeData['identifier']['reference_entity_identifier'] = 'designer';
        $attributeData['reference_entity_identifier'] = 'designer';
        $attributeData['code'] = $attributeCode;
        $attributeData['order'] = (int) $attributeData['order'];
        $attributeData['is_required'] = json_decode($attributeData['is_required']);
        $attributeData['value_per_channel'] = json_decode($attributeData['value_per_channel']);
        $attributeData['value_per_locale'] = json_decode($attributeData['value_per_locale']);
        $attributeData['labels'] = json_decode($attributeData['labels'], true);

        $command = $this->commandFactoryRegistry->getFactory($attributeData)->create($attributeData);
        $this->constraintViolationsContext->addViolations($this->validator->validate($command));

        try {
            ($this->handler)($command);
        } catch (\Exception $e) {
            $this->exceptionContext->setException($e);
        }
    }

    /**
     * @When /^the user creates an option collection attribute "([^"]*)"$/
     */
    public function theUserCreatesAnOptionCollectionAttribute(string $attributeCode, TableNode $attributeData): void
    {
        $attributeData = current($attributeData->getHash());

        $attributeData['type'] = 'option_collection';
        $attributeData['identifier']['identifier'] = $attributeCode;
        $attributeData['identifier']['reference_entity_identifier'] = 'designer';
        $attributeData['reference_entity_identifier'] = 'designer';
        $attributeData['code'] = $attributeCode;
        $attributeData['order'] = (int) $attributeData['order'];
        $attributeData['is_required'] = json_decode($attributeData['is_required']);
        $attributeData['value_per_channel'] = json_decode($attributeData['value_per_channel']);
        $attributeData['value_per_locale'] = json_decode($attributeData['value_per_locale']);
        $attributeData['labels'] = json_decode($attributeData['labels'], true);

        $command = $this->commandFactoryRegistry->getFactory($attributeData)->create($attributeData);
        $this->constraintViolationsContext->addViolations($this->validator->validate($command));

        try {
            ($this->handler)($command);
        } catch (\Exception $e) {
            $this->exceptionContext->setException($e);
        }
    }

    /**
     * @When /^the user creates an option attribute "([^"]*)" with:$/
     */
    public function theUserCreatesAnOptionAttributeLinkedToTheReferenceEntityWith(
        string $attributeCode,
        TableNode $attributeData
    ): void {
        $attributeData = current($attributeData->getHash());
        $attributeData['type'] = 'option';
        $attributeData['identifier']['identifier'] = $attributeCode;
        $attributeData['identifier']['reference_entity_identifier'] = 'designer';
        $attributeData['reference_entity_identifier'] = 'designer';
        $attributeData['code'] = $attributeCode;
        $attributeData['order'] = (int) $attributeData['order'];
        $attributeData['is_required'] = json_decode($attributeData['is_required']);
        $attributeData['value_per_channel'] = json_decode($attributeData['value_per_channel']);
        $attributeData['value_per_locale'] = json_decode($attributeData['value_per_locale']);
        $attributeData['labels'] = json_decode($attributeData['labels'], true);

        $command = $this->commandFactoryRegistry->getFactory($attributeData)->create($attributeData);
        $this->constraintViolationsContext->addViolations($this->validator->validate($command));

        try {
            ($this->handler)($command);
        } catch (\Exception $e) {
            $this->exceptionContext->setException($e);
        }
    }

    /**
     * @Then /^there is an option attribute "([^"]*)" with:$/
     */
    public function thereIsAnOptionAttributeWith(string $attributeCode, TableNode $attributeData): void
    {
        $attributeIdentifier = $this->attributeRepository->nextIdentifier(
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString($attributeCode)
        );
        $expected = current($attributeData->getHash());
        $expected['code'] = $attributeCode;
        $expected['identifier'] = (string) $attributeIdentifier;
        $expected['reference_entity_identifier'] = 'designer';
        $expected['labels'] = json_decode($expected['labels'], true);
        $expected['order'] = (int) $expected['order'];
        $expected['is_required'] = json_decode($expected['is_required']);
        $expected['value_per_channel'] = json_decode($expected['value_per_channel']);
        $expected['value_per_locale'] = json_decode($expected['value_per_locale']);
        $expected['options'] = [];
        ksort($expected);

        $attribute = $this->attributeRepository->getByIdentifier($attributeIdentifier);
        $actual = $attribute->normalize();
        ksort($actual);

        Assert::assertEquals($expected, $actual);
    }

    /**
     * @When /^the user creates an option collection attribute "([^"]*)" with:$/
     */
    public function theUserCreatesAnOptionCollectionAttributeWith(string $attributeCode, TableNode $attributeData): void
    {
        $attributeData = current($attributeData->getHash());
        $attributeData['type'] = 'option_collection';
        $attributeData['identifier']['identifier'] = $attributeCode;
        $attributeData['identifier']['reference_entity_identifier'] = 'designer';
        $attributeData['reference_entity_identifier'] = 'designer';
        $attributeData['code'] = $attributeCode;
        $attributeData['order'] = (int) $attributeData['order'];
        $attributeData['is_required'] = json_decode($attributeData['is_required']);
        $attributeData['value_per_channel'] = json_decode($attributeData['value_per_channel']);
        $attributeData['value_per_locale'] = json_decode($attributeData['value_per_locale']);
        $attributeData['labels'] = json_decode($attributeData['labels'], true);

        $command = $this->commandFactoryRegistry->getFactory($attributeData)->create($attributeData);
        $this->constraintViolationsContext->addViolations($this->validator->validate($command));

        try {
            ($this->handler)($command);
        } catch (\Exception $e) {
            $this->exceptionContext->setException($e);
        }
    }

    /**
     * @Then /^there is an option collection attribute "([^"]*)" with:$/
     */
    public function thereIsAnOptionCollectionAttributeWith(string $attributeCode, TableNode $attributeData): void
    {
        $attributeIdentifier = $this->attributeRepository->nextIdentifier(
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString($attributeCode)
        );
        $expected = current($attributeData->getHash());
        $expected['identifier'] = (string) $attributeIdentifier;
        $expected['reference_entity_identifier'] = 'designer';
        $expected['labels'] = json_decode($expected['labels'], true);
        $expected['order'] = (int) $expected['order'];
        $expected['is_required'] = json_decode($expected['is_required']);
        $expected['value_per_channel'] = json_decode($expected['value_per_channel']);
        $expected['value_per_locale'] = json_decode($expected['value_per_locale']);
        $expected['options'] = [];
        $expected['code'] = $attributeCode;
        ksort($expected);

        $attribute = $this->attributeRepository->getByIdentifier($attributeIdentifier);
        $actual = $attribute->normalize();
        ksort($actual);

        Assert::assertEquals($expected, $actual);
    }
}
