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

namespace Akeneo\EnrichedEntity\tests\back\Acceptance\Context;

use Akeneo\EnrichedEntity\Application\Attribute\CreateAttribute\CommandFactory\CreateAttributeCommandFactoryRegistryInterface;
use Akeneo\EnrichedEntity\Application\Attribute\CreateAttribute\CreateAttributeHandler;
use Akeneo\EnrichedEntity\Application\Attribute\CreateAttribute\CreateImageAttributeCommand;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\EnrichedEntity\Domain\Repository\AttributeNotFoundException;
use Akeneo\EnrichedEntity\Domain\Repository\AttributeRepositoryInterface;
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
     * @When /^the user creates a text attribute "([^"]*)" linked to the enriched entity "([^"]*)" with:$/
     */
    public function theUserCreatesATextAttributeLinkedToTheEnrichedEntityWith(string $attributeCode, string $enrichedEntityIdentifier, TableNode $attributeData): void
    {
        $attributeData = current($attributeData->getHash());

        $attributeData['type'] = 'text';
        $attributeData['identifier']['identifier'] = $attributeData['code'];
        $attributeData['identifier']['enriched_entity_identifier'] = $enrichedEntityIdentifier;
        $attributeData['enriched_entity_identifier'] = $enrichedEntityIdentifier;
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
     * @Then /^there is a text attribute "([^"]*)" in the enriched entity "([^"]*)" with:$/
     */
    public function thereIsAnTextAttributeInTheEnrichedEntityWith(
        string $attributeCode,
        string $enrichedEntityIdentifier,
        TableNode $attributeData
    ) {
        $expected = current($attributeData->getHash());
        $expected['identifier'] = [
            'enriched_entity_identifier' => $enrichedEntityIdentifier,
            'identifier' => $attributeCode,
        ];
        $expected['enriched_entity_identifier'] = $enrichedEntityIdentifier;
        $expected['labels'] = json_decode($expected['labels'], true);
        $expected['order'] = (int) $expected['order'];
        $expected['is_required'] = (bool) $expected['is_required'];
        $expected['value_per_channel'] = (bool) $expected['value_per_channel'];
        $expected['value_per_locale'] = (bool) $expected['value_per_locale'];
        $expected['max_length'] = (int) $expected['max_length'];
        $expected['is_text_area'] = '' === $expected['is_text_area'] ? null : (bool) $expected['is_text_area'];
        $expected['is_rich_text_editor'] = '' === $expected['is_rich_text_editor'] ? null : (bool) $expected['is_rich_text_editor'];
        $expected['validation_rule'] = '' === $expected['validation_rule'] ? null : $expected['validation_rule'];
        $expected['regular_expression'] = '' === $expected['regular_expression'] ? null : $expected['regular_expression'];
        ksort($expected);

        $attribute = $this->attributeRepository->getByIdentifier(
            AttributeIdentifier::create($enrichedEntityIdentifier, $attributeCode)
        );
        $actual = $attribute->normalize();
        ksort($actual);

        Assert::assertSame($expected, $actual);
    }

    /**
     * @Then /^there is no attribute "([^"]*)" for the enriched entity "([^"]*)"$/
     */
    public function thereIsNoAttributeInTheEnrichedEntity(
        string $attributeCode,
        string $enrichedEntityIdentifier
    ) {
        $attribute = null;

        try {
            $attribute = $this->attributeRepository->getByIdentifier(
                AttributeIdentifier::create($enrichedEntityIdentifier, $attributeCode)
            );
            Assert::assertTrue(false);
        } catch (AttributeNotFoundException $e) {
            Assert::assertNull($attribute);
        }
    }

    /**
     * @When /^the user creates an image attribute "([^"]*)" linked to the enriched entity "([^"]*)" with:$/
     */
    public function theUserCreatesAnImageAttributeLinkedToTheEnrichedEntityWith(
        $attributeCode,
        $enrichedEntityIdentifier,
        TableNode $attributeData
    ) {
        $attributeData = current($attributeData->getHash());

        $attributeData['type'] = 'image';
        $attributeData['identifier']['identifier'] = $attributeData['code'];
        $attributeData['identifier']['enriched_entity_identifier'] = $enrichedEntityIdentifier;
        $attributeData['enriched_entity_identifier'] = $enrichedEntityIdentifier;
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
     * @Then /^there is an image attribute "([^"]*)" in the enriched entity "([^"]*)" with:$/
     */
    public function thereIsAnAttributeWith(
        string $attributeCode,
        string $enrichedEntityIdentifier,
        TableNode $attributeData
    ) {
        $expected = current($attributeData->getHash());
        $expected['identifier'] = [
            'enriched_entity_identifier' => $enrichedEntityIdentifier,
            'identifier' => $attributeCode,
        ];
        $expected['enriched_entity_identifier'] = $enrichedEntityIdentifier;
        $expected['labels'] = json_decode($expected['labels'], true);
        $expected['order'] = (int) $expected['order'];
        $expected['is_required'] = (bool) $expected['is_required'];
        $expected['value_per_channel'] = (bool) $expected['value_per_channel'];
        $expected['value_per_locale'] = (bool) $expected['value_per_locale'];
        $expected['allowed_extensions'] = json_decode($expected['allowed_extensions']);
        ksort($expected);

        $attribute = $this->attributeRepository->getByIdentifier(
            AttributeIdentifier::create($enrichedEntityIdentifier, $attributeCode)
        );
        $actual = $attribute->normalize();
        ksort($actual);

        Assert::assertSame($expected, $actual);
    }
}
