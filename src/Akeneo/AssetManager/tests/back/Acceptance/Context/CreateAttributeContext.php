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

use Akeneo\AssetManager\Application\Attribute\CreateAttribute\CommandFactory\CreateAttributeCommandFactoryRegistryInterface;
use Akeneo\AssetManager\Application\Attribute\CreateAttribute\CreateAttributeHandler;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Repository\AttributeNotFoundException;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;
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
     * @When /^the user creates a text attribute "([^"]*)" linked to the asset family "([^"]*)" with:$/
     */
    public function theUserCreatesATextAttributeLinkedToTheAssetFamilyWith(string $attributeCode, string $assetFamilyIdentifier, TableNode $attributeData): void
    {
        $attributeData = current($attributeData->getHash());

        $attributeData['type'] = 'text';
        $attributeData['identifier']['identifier'] = $attributeCode;
        $attributeData['identifier']['asset_family_identifier'] = $assetFamilyIdentifier;
        $attributeData['asset_family_identifier'] = $assetFamilyIdentifier;
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
     * @Then /^there is a text attribute "([^"]*)" in the asset family "([^"]*)" with:$/
     */
    public function thereIsAnTextAttributeInTheAssetFamilyWith(
        string $attributeCode,
        string $assetFamilyIdentifier,
        TableNode $attributeData
    ) {
        $attributeIdentifier = $this->attributeRepository->nextIdentifier(
            AssetFamilyIdentifier::fromString($assetFamilyIdentifier),
            AttributeCode::fromString($attributeCode)
        );

        $expected = current($attributeData->getHash());
        $expected['identifier'] = (string) $attributeIdentifier;
        $expected['asset_family_identifier'] = $assetFamilyIdentifier;
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
     * @When /^the user creates a asset attribute "([^"]*)" linked to the asset family "([^"]*)" with:$/
     */
    public function theUserCreatesAAssetAttributeLinkedToTheAssetFamilyWith(string $attributeCode, string $assetFamilyIdentifier, TableNode $attributeData): void
    {
        $attributeData = current($attributeData->getHash());

        $attributeData['type'] = 'asset';
        $attributeData['identifier']['identifier'] = $attributeCode;
        $attributeData['identifier']['asset_family_identifier'] = $assetFamilyIdentifier;
        $attributeData['asset_family_identifier'] = $assetFamilyIdentifier;
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
     * @Then /^there is a asset attribute "([^"]*)" in the asset family "([^"]*)" with:$/
     */
    public function thereIsAAssetAttributeInTheAssetFamilyWith(
        string $attributeCode,
        string $assetFamilyIdentifier,
        TableNode $attributeData
    ) {
        $attributeIdentifier = $this->attributeRepository->nextIdentifier(
            AssetFamilyIdentifier::fromString($assetFamilyIdentifier),
            AttributeCode::fromString($attributeCode)
        );

        $expected = current($attributeData->getHash());
        $expected['identifier'] = (string) $attributeIdentifier;
        $expected['asset_family_identifier'] = $assetFamilyIdentifier;
        $expected['labels'] = json_decode($expected['labels'], true);
        $expected['order'] = (int) $expected['order'];
        $expected['is_required'] = json_decode($expected['is_required']);
        $expected['value_per_channel'] = json_decode($expected['value_per_channel']);
        $expected['value_per_locale'] = json_decode($expected['value_per_locale']);
        $expected['asset_type'] = (string) $expected['asset_type'];
        ksort($expected);

        $attribute = $this->attributeRepository->getByIdentifier($attributeIdentifier);
        $actual = $attribute->normalize();
        ksort($actual);

        Assert::assertSame($expected, $actual);
    }

    /**
     * @When /^the user creates a asset collection attribute "([^"]*)" linked to the asset family "([^"]*)" with:$/
     */
    public function theUserCreatesAAssetCollectionAttributeLinkedToTheAssetFamilyWith(string $attributeCode, string $assetFamilyIdentifier, TableNode $attributeData): void
    {
        $attributeData = current($attributeData->getHash());

        $attributeData['type'] = 'asset_collection';
        $attributeData['identifier']['identifier'] = $attributeCode;
        $attributeData['identifier']['asset_family_identifier'] = $assetFamilyIdentifier;
        $attributeData['asset_family_identifier'] = $assetFamilyIdentifier;
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
     * @Then /^there is a asset collection attribute "([^"]*)" in the asset family "([^"]*)" with:$/
     */
    public function thereIsAAssetCollectionAttributeInTheAssetFamilyWith(
        string $attributeCode,
        string $assetFamilyIdentifier,
        TableNode $attributeData
    ) {
        $attributeIdentifier = $this->attributeRepository->nextIdentifier(
            AssetFamilyIdentifier::fromString($assetFamilyIdentifier),
            AttributeCode::fromString($attributeCode)
        );

        $expected = current($attributeData->getHash());
        $expected['identifier'] = (string) $attributeIdentifier;
        $expected['asset_family_identifier'] = $assetFamilyIdentifier;
        $expected['labels'] = json_decode($expected['labels'], true);
        $expected['order'] = (int) $expected['order'];
        $expected['is_required'] = json_decode($expected['is_required']);
        $expected['value_per_channel'] = json_decode($expected['value_per_channel']);
        $expected['value_per_locale'] = json_decode($expected['value_per_locale']);
        $expected['asset_type'] = (string) $expected['asset_type'];
        ksort($expected);

        $attribute = $this->attributeRepository->getByIdentifier($attributeIdentifier);
        $actual = $attribute->normalize();
        ksort($actual);

        Assert::assertSame($expected, $actual);
    }

    /**
     * @Then /^there is no attribute "([^"]*)" for the asset family "([^"]*)"$/
     */
    public function thereIsNoAttributeInTheAssetFamily(
        string $attributeCode,
        string $assetFamilyIdentifier
    ) {
        $attribute = null;
        $attributeIdentifier = $this->attributeRepository->nextIdentifier(
            AssetFamilyIdentifier::fromString($assetFamilyIdentifier),
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
     * @When /^the user creates an image attribute "([^"]*)" linked to the asset family "([^"]*)" with:$/
     */
    public function theUserCreatesAnImageAttributeLinkedToTheAssetFamilyWith(
        $attributeCode,
        $assetFamilyIdentifier,
        TableNode $attributeData
    ) {
        $attributeData = current($attributeData->getHash());

        $attributeData['type'] = 'image';
        $attributeData['code'] = $attributeCode;
        $attributeData['asset_family_identifier'] = $assetFamilyIdentifier;
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
     * @Then /^there is an image attribute "([^"]*)" in the asset family "([^"]*)" with:$/
     */
    public function thereIsAnAttributeWith(
        string $attributeCode,
        string $assetFamilyIdentifier,
        TableNode $attributeData
    ) {
        $attributeIdentifier = $this->attributeRepository->nextIdentifier(
            AssetFamilyIdentifier::fromString($assetFamilyIdentifier),
            AttributeCode::fromString($attributeCode)
        );

        $expected = current($attributeData->getHash());
        $expected['identifier'] = (string) $attributeIdentifier;
        $expected['code'] = $attributeCode;
        $expected['asset_family_identifier'] = $assetFamilyIdentifier;
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
     * @Given /^(\d+) random attributes for an asset family$/
     */
    public function randomAttributesForAssetFamily(int $number)
    {
        for ($i = 2; $i < $number; $i++) {
            $attributeCode = uniqid();
            $attributeData['type'] = 'text';
            $attributeData['identifier']['identifier'] = $attributeCode;
            $attributeData['identifier']['asset_family_identifier'] = 'designer';
            $attributeData['asset_family_identifier'] = 'designer';
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
        $attributeData['identifier']['asset_family_identifier'] = 'designer';
        $attributeData['asset_family_identifier'] = 'designer';
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
        $attributeData['identifier']['asset_family_identifier'] = 'designer';
        $attributeData['asset_family_identifier'] = 'designer';
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
    public function theUserCreatesAnOptionAttributeLinkedToTheAssetFamilyWith(
        string $attributeCode,
        TableNode $attributeData
    ): void {
        $attributeData = current($attributeData->getHash());
        $attributeData['type'] = 'option';
        $attributeData['identifier']['identifier'] = $attributeCode;
        $attributeData['identifier']['asset_family_identifier'] = 'designer';
        $attributeData['asset_family_identifier'] = 'designer';
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
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString($attributeCode)
        );
        $expected = current($attributeData->getHash());
        $expected['code'] = $attributeCode;
        $expected['identifier'] = (string) $attributeIdentifier;
        $expected['asset_family_identifier'] = 'designer';
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
        $attributeData['identifier']['asset_family_identifier'] = 'designer';
        $attributeData['asset_family_identifier'] = 'designer';
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
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString($attributeCode)
        );
        $expected = current($attributeData->getHash());
        $expected['identifier'] = (string) $attributeIdentifier;
        $expected['asset_family_identifier'] = 'designer';
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

    /**
     * @When /^the user creates a number attribute "([^"]*)" to the asset family "([^"]*)" with:$/
     */
    public function theUserCreatesANumberAttributeToTheAssetFamilyWith(string $attributeCode, string $assetFamilyIdentifier, TableNode $attributeData): void
    {
        $attributeData = current($attributeData->getHash());

        $attributeData['type'] = 'number';
        $attributeData['identifier']['identifier'] = $attributeCode;
        $attributeData['identifier']['asset_family_identifier'] = $assetFamilyIdentifier;
        $attributeData['asset_family_identifier'] = $assetFamilyIdentifier;
        $attributeData['code'] = $attributeCode;
        $attributeData['order'] = (int) $attributeData['order'];
        $attributeData['is_required'] = json_decode($attributeData['is_required']);
        $attributeData['value_per_channel'] = json_decode($attributeData['value_per_channel']);
        $attributeData['value_per_locale'] = json_decode($attributeData['value_per_locale']);
        $attributeData['labels'] = json_decode($attributeData['labels'], true);
        $attributeData['decimals_allowed'] = json_decode($attributeData['decimals_allowed']);

        $command = $this->commandFactoryRegistry->getFactory($attributeData)->create($attributeData);
        $this->constraintViolationsContext->addViolations($this->validator->validate($command));

        try {
            ($this->handler)($command);
        } catch (\Exception $e) {
            $this->exceptionContext->setException($e);
        }
    }

    /**
     * @Then /^there is a number attribute "([^"]*)" in the asset family "([^"]*)" with:$/
     */
    public function thereIsANumberAttributeInTheAssetFamilyWith(string $attributeCode, string $assetFamilyIdentifier, TableNode $expected): void
    {
        $attributeIdentifier = $this->attributeRepository->nextIdentifier(
            AssetFamilyIdentifier::fromString($assetFamilyIdentifier),
            AttributeCode::fromString($attributeCode)
        );

        $expected = current($expected->getHash());
        $expected['identifier'] = (string) $attributeIdentifier;
        $expected['asset_family_identifier'] = $assetFamilyIdentifier;
        $expected['code'] = $attributeCode;
        $expected['order'] = (int)$expected['order'];
        $expected['is_required'] = json_decode($expected['is_required']);
        $expected['value_per_channel'] = json_decode($expected['value_per_channel']);
        $expected['value_per_locale'] = json_decode($expected['value_per_locale']);
        $expected['labels'] = json_decode($expected['labels'], true);
        $expected['decimals_allowed'] = json_decode($expected['decimals_allowed']);

        $attribute = $this->attributeRepository->getByIdentifier($attributeIdentifier);
        $actual = $attribute->normalize();
        ksort($actual);
        ksort($expected);

        Assert::assertEquals($expected, $actual);
    }

    /**
     * @When /^the user creates a media_link attribute "([^"]*)" to the asset family "([^"]*)" with:$/
     */
    public function theUserCreatesAnMediaLinkAttributeToTheAssetFamilyWith(string $attributeCode, string $assetFamilyIdentifier, TableNode $attributeData): void
    {
        $attributeData = current($attributeData->getHash());

        $attributeData['type'] = 'media_link';
        $attributeData['identifier']['identifier'] = $attributeCode;
        $attributeData['identifier']['asset_family_identifier'] = $assetFamilyIdentifier;
        $attributeData['asset_family_identifier'] = $assetFamilyIdentifier;
        $attributeData['code'] = $attributeCode;
        $attributeData['order'] = (int) $attributeData['order'];
        $attributeData['is_required'] = json_decode($attributeData['is_required']);
        $attributeData['value_per_channel'] = json_decode($attributeData['value_per_channel']);
        $attributeData['value_per_locale'] = json_decode($attributeData['value_per_locale']);
        $attributeData['labels'] = json_decode($attributeData['labels'], true);
        $attributeData['prefix'] = json_decode($attributeData['prefix']);
        $attributeData['suffix'] = json_decode($attributeData['suffix']);

        $command = $this->commandFactoryRegistry->getFactory($attributeData)->create($attributeData);
        $this->constraintViolationsContext->addViolations($this->validator->validate($command));

        try {
            ($this->handler)($command);
        } catch (\Exception $e) {
            $this->exceptionContext->setException($e);
        }
    }

    /**
     * @Then /^there is an media_link attribute "([^"]*)" in the asset family "([^"]*)" with:$/
     */
    public function thereIsAnMediaLinkAttributeInTheAssetFamilyWith(string $attributeCode, string $assetFamilyIdentifier, TableNode $expected): void
    {
        $attributeIdentifier = $this->attributeRepository->nextIdentifier(
            AssetFamilyIdentifier::fromString($assetFamilyIdentifier),
            AttributeCode::fromString($attributeCode)
        );

        $expected = current($expected->getHash());
        $expected['identifier'] = (string) $attributeIdentifier;
        $expected['asset_family_identifier'] = $assetFamilyIdentifier;
        $expected['code'] = $attributeCode;
        $expected['order'] = (int)$expected['order'];
        $expected['is_required'] = json_decode($expected['is_required']);
        $expected['value_per_channel'] = json_decode($expected['value_per_channel']);
        $expected['value_per_locale'] = json_decode($expected['value_per_locale']);
        $expected['labels'] = json_decode($expected['labels'], true);
        $expected['prefix'] = json_decode($expected['prefix']);
        $expected['suffix'] = json_decode($expected['suffix']);

        $attribute = $this->attributeRepository->getByIdentifier($attributeIdentifier);
        $actual = $attribute->normalize();
        ksort($actual);
        ksort($expected);

        Assert::assertEquals($expected, $actual);
    }
}
