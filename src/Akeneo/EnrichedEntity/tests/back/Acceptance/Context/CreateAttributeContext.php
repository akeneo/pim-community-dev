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

use Akeneo\EnrichedEntity\Application\Attribute\CreateAttribute\CreateAttributeHandler;
use Akeneo\EnrichedEntity\Application\Attribute\CreateAttribute\CreateImageAttributeCommand;
use Akeneo\EnrichedEntity\Application\Attribute\CreateAttribute\CreateTextAttributeCommand;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Repository\AttributeNotFoundException;
use Akeneo\EnrichedEntity\Domain\Repository\AttributeRepositoryInterface;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use PHPUnit\Framework\Assert;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class CreateAttributeContext implements Context
{
    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var CreateAttributeHandler */
    private $handler;

    /** @var ExceptionContext */
    private $exceptionContext;

    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        CreateAttributeHandler $handler,
        ExceptionContext $exceptionContext
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->handler = $handler;
        $this->exceptionContext = $exceptionContext;
    }

    /**
     * @When /^the user creates a text attribute "([^"]*)" linked to the enriched entity "([^"]*)" with:$/
     */
    public function theUserCreatesATextAttributeLinkedToTheEnrichedEntityWith(string $attributeCode, string $enrichedEntityIdentifier, TableNode $attributeData): void
    {
        $attributeData = current($attributeData->getHash());
        $command = new CreateTextAttributeCommand();
        $command->identifier = [
            'identifier' => $attributeCode,
            'enriched_entity_identifier' => $enrichedEntityIdentifier
        ];
        $command->code = $attributeCode;
        $command->enrichedEntityIdentifier = $enrichedEntityIdentifier;
        $command->labels = json_decode($attributeData['labels'], true);
        $command->order = (int) $attributeData['order'];
        $command->required = (bool) $attributeData['required'];
        $command->valuePerChannel = (bool) $attributeData['value_per_channel'];
        $command->valuePerLocale = (bool) $attributeData['value_per_locale'];
        $command->maxLength = (int) $attributeData['max_length'];

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
        $expected['required'] = (bool) $expected['required'];
        $expected['value_per_channel'] = (bool) $expected['value_per_channel'];
        $expected['value_per_locale'] = (bool) $expected['value_per_locale'];
        $expected['max_length'] = (int) $expected['max_length'];
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
        $command = new CreateImageAttributeCommand();
        $command->identifier = [
            'identifier' => $attributeCode,
            'enriched_entity_identifier' => $enrichedEntityIdentifier
        ];
        $command->code = $attributeCode;
        $command->enrichedEntityIdentifier = $enrichedEntityIdentifier;
        $command->labels = json_decode($attributeData['labels'], true);
        $command->order = (int) $attributeData['order'];
        $command->required = (bool) $attributeData['required'];
        $command->valuePerChannel = (bool) $attributeData['value_per_channel'];
        $command->valuePerLocale = (bool) $attributeData['value_per_locale'];
        $command->maxFileSize = $attributeData['max_file_size'];
        $command->allowedExtensions = json_decode($attributeData['allowed_extensions']);

        ($this->handler)($command);
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
        $expected['required'] = (bool) $expected['required'];
        $expected['value_per_channel'] = (bool) $expected['value_per_channel'];
        $expected['value_per_locale'] = (bool) $expected['value_per_locale'];
        $expected['max_file_size'] = $expected['max_file_size'];
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
