<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Test\Pim\TableAttribute\Acceptance\Context;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectOptionCollection;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnCode;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\WriteSelectOptionCollection;
use Akeneo\Test\Acceptance\Attribute\InMemoryAttributeRepository;
use Akeneo\Test\Common\Structure\Attribute\Builder;
use Akeneo\Test\Pim\TableAttribute\Acceptance\InMemory\InMemorySelectOptionCollectionRepository;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 */
final class CreateAttributeContext implements Context
{
    private Builder $attributeBuilder;
    private ValidatorInterface $validator;
    private InMemoryAttributeRepository $attributeRepository;
    private InMemorySelectOptionCollectionRepository $collectionRepository;
    private ConstraintViolationsContext $constraintViolationsContext;

    public function __construct(
        ValidatorInterface $validator,
        InMemoryAttributeRepository $attributeRepository,
        InMemorySelectOptionCollectionRepository $collectionRepository,
        ConstraintViolationsContext $constraintViolationsContext
    ) {
        $this->attributeBuilder = new Builder();
        $this->validator = $validator;
        $this->attributeRepository = $attributeRepository;
        $this->collectionRepository = $collectionRepository;
        $this->constraintViolationsContext = $constraintViolationsContext;
    }

    /**
     * @When I create a table attribute with a valid configuration
     */
    public function iCreateATableAttributeWithAValidConfiguration(): void
    {
        $this->createValidAttribute('table');
    }

    /**
     * @When I create a table attribute without table configuration
     */
    public function iCreateATableAttributeWithoutConfiguration(): void
    {
        $attribute = $this->attributeBuilder
            ->withCode('table')
            ->withGroupCode('marketing')
            ->withType(AttributeTypes::TABLE)
            ->build();
        $this->saveAttribute($attribute);
    }

    /**
     * @When I create a table attribute with a configuration with only one column
     */
    public function iCreateATableAttributeWithAConfigurationWithOnlyOneColumn(): void
    {
        $attribute = $this->attributeBuilder
            ->withCode('table')
            ->withGroupCode('marketing')
            ->withType(AttributeTypes::TABLE)
            ->build();
        $attribute->setRawTableConfiguration([
            [
                'data_type' => 'select',
                'code' => 'ingredients',
            ]
        ]);
        $this->saveAttribute($attribute);
    }

    /**
     * @When I create a table attribute with a configuration with too many columns
     */
    public function iCreateATableAttributeWithAConfigurationWithTooManyColumns(): void
    {
        $attribute = $this->attributeBuilder
            ->withCode('table')
            ->withGroupCode('marketing')
            ->withType(AttributeTypes::TABLE)
            ->build();
        $rawConfiguration = [];
        for ($i = 0; $i < 11; $i++) {
            $rawConfiguration[] = [
                'data_type' => 'select',
                'code' => sprintf('column_%d', $i),
            ];
        }
        $attribute->setRawTableConfiguration($rawConfiguration);
        $this->saveAttribute($attribute);
    }

    /**
     * @When /^I create a table attribute with a configuration having column code "([^"]*)"$/
     */
    public function iCreateATableAttributeWithAConfigurationHavingColumnCode(string $code): void
    {
        $this->iCreateATableAttributeWithAConfiguration(sprintf('{"data_type": "text", "code": "%s"}', $code));
    }

    /**
     * @When I create a table attribute with a configuration having a label with :number characters
     */
    public function iCreateATableAttributeWithAConfigurationHavingALabelWithCharacters(int $number): void
    {
        $label = str_repeat('a', $number);
        $this->iCreateATableAttributeWithAConfiguration(sprintf('{"data_type": "text", "code": "ingredient", "labels": {"en_US": "%s"}}', $label));
    }

    /**
     * @When /^I create a table attribute with a configuration '([^']*)'$/
     */
    public function iCreateATableAttributeWithAConfiguration(string $jsonAsString): void
    {
        $json = \json_decode($jsonAsString, true, 512, JSON_THROW_ON_ERROR);

        $attribute = $this->attributeBuilder
            ->withCode('table')
            ->withGroupCode('marketing')
            ->withType(AttributeTypes::TABLE)
            ->build();
        $attribute->setRawTableConfiguration([
            [
                'data_type' => 'select',
                'code' => 'ingredients',
            ], $json,
        ]);
        $this->saveAttribute($attribute);
    }

    /**
     * @When /^I create a table attribute with a configuration having column codes "([^"]*)"$/
     */
    public function iCreateATableAttributeWithAConfigurationHavingColumnCodes(string $codes): void
    {
        $rawTableConfiguration = [
            ['data_type' => 'select', 'code' => 'ingredients'],
        ];
        $codes = explode(',', $codes);
        foreach ($codes as $code) {
            $rawTableConfiguration[] = ['data_type' => 'text', 'code' => $code];
        }

        $this->iCreateATableAttributeWithAConfiguration(sprintf('{"data_type": "text", "code": "%s"}', $code));

        $attribute = $this->attributeBuilder
            ->withCode('table')
            ->withGroupCode('marketing')
            ->withType(AttributeTypes::TEXT)
            ->build();
        $attribute->setRawTableConfiguration($rawTableConfiguration);
        $this->saveAttribute($attribute);
    }

    /**
     * @When I create a text attribute with a table configuration
     */
    public function iCreateATextAttributeWithAConfiguration(): void
    {
        $attribute = $this->attributeBuilder
            ->withCode('table')
            ->withGroupCode('marketing')
            ->withType(AttributeTypes::TEXT)
            ->build();
        $attribute->setRawTableConfiguration([
            [
                'data_type' => 'select',
                'code' => 'ingredients',
            ]
        ]);
        $this->saveAttribute($attribute);
    }

    /**
     * @When I create a table attribute with text first column
     */
    public function iCreateATableAttributeWithTextFirstColumn(): void
    {
        $attribute = $this->attributeBuilder
            ->withCode('table')
            ->withGroupCode('marketing')
            ->withType(AttributeTypes::TABLE)
            ->build();
        $attribute->setRawTableConfiguration([
            [
                'data_type' => 'text',
                'code' => 'ingredients',
            ],
            [
                'data_type' => 'number',
                'code' => 'quantity',
            ],
        ]);
        $this->saveAttribute($attribute);
    }

    private function saveAttribute(AttributeInterface $attribute): void
    {
        $violations = $this->validator->validate($attribute);
        if (0 < $violations->count()) {
            $this->constraintViolationsContext->add($violations);
            return;
        }

        $this->attributeRepository->save($attribute);
    }

    /**
     * @When I create a table attribute with too much options
     */
    public function iCreateATableAttributeWithTooMuchOptions(): void
    {
        $options = [];
        for ($i = 0; $i < 20001; $i++) {
            $options[] = ['code' => sprintf('code_%s', $i)];
        }

        $attribute = $this->attributeBuilder
            ->withCode('table')
            ->withGroupCode('marketing')
            ->withType(AttributeTypes::TABLE)
            ->build();
        $attribute->setRawTableConfiguration([
            [
                'data_type' => 'select',
                'code' => 'ingredients',
                'options' => $options,
            ],
            [
                'data_type' => 'text',
                'code' => 'description',
            ],
        ]);
        $this->saveAttribute($attribute);
    }

    /**
     * @Given the following select options:
     */
    public function theFollowingSelectOptions(TableNode $table): void
    {
        foreach ($table as $row) {
            $this->collectionRepository->save(
                $row['attribute_code'],
                ColumnCode::fromString($row['column_code']),
                WriteSelectOptionCollection::fromReadSelectOptionCollection(
                    SelectOptionCollection::fromNormalized(\json_decode($row['options'], true))
                )
            );
        }
    }

    /**
     * @Given :tableAttributeCount table attributes
     */
    public function tableAttributes(int $tableAttributeCount)
    {
        for ($i = 0; $i < $tableAttributeCount; $i++) {
            $this->createValidAttribute(sprintf('table_attribute_%d', $i));
        }
    }

    private function createValidAttribute(string $attributeCode): void
    {
        $attribute = $this->attributeBuilder
            ->withCode($attributeCode)
            ->withGroupCode('marketing')
            ->withType(AttributeTypes::TABLE)
            ->build();
        $attribute->setRawTableConfiguration([
            [
                'data_type' => 'select',
                'code' => 'ingredients',
                'options' => [
                    ['code' => 'sugar'],
                    ['code' => 'salt'],
                ],
            ],
            [
                'data_type' => 'number',
                'code' => 'quantity',
            ],
            [
                'data_type' => 'boolean',
                'code' => 'isAllergenic',
            ],
            [
                'data_type' => 'text',
                'code' => 'comments',
            ],
        ]);
        $this->saveAttribute($attribute);
    }
}
