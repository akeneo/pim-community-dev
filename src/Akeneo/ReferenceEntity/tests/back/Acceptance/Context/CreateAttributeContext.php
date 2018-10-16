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
        $attributeData['is_required'] = (bool) $attributeData['is_required'];
        $attributeData['value_per_channel'] = (bool) $attributeData['value_per_channel'];
        $attributeData['value_per_locale'] = (bool) $attributeData['value_per_locale'];
        $attributeData['labels'] = json_decode($attributeData['labels'], true);
        $attributeData['max_length'] = (int) $attributeData['max_length'];

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
        $expected['is_required'] = (bool) $expected['is_required'];
        $expected['value_per_channel'] = (bool) $expected['value_per_channel'];
        $expected['value_per_locale'] = (bool) $expected['value_per_locale'];
        $expected['max_length'] = (int) $expected['max_length'];
        $expected['is_textarea'] = '' === $expected['is_textarea'] ? null : (bool) $expected['is_textarea'];
        $expected['is_rich_text_editor'] = '' === $expected['is_rich_text_editor'] ? null : (bool) $expected['is_rich_text_editor'];
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
        $this->constraintViolationsContext->addViolations($this->validator->validate($command));

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
        $attributeData['is_required'] = (bool) $attributeData['is_required'];
        $attributeData['value_per_channel'] = (bool) $attributeData['value_per_channel'];
        $attributeData['value_per_locale'] = (bool) $attributeData['value_per_locale'];
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
        $expected['is_required'] = (bool) $expected['is_required'];
        $expected['value_per_channel'] = (bool) $expected['value_per_channel'];
        $expected['value_per_locale'] = (bool) $expected['value_per_locale'];
        $expected['allowed_extensions'] = json_decode($expected['allowed_extensions']);
        ksort($expected);

        $attribute = $this->attributeRepository->getByIdentifier($attributeIdentifier);
        $actual = $attribute->normalize();
        ksort($actual);

        Assert::assertSame($expected, $actual);
    }
}
