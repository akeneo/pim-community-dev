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

namespace Akeneo\Pim\TableAttribute\tests\back\Acceptance\Context;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Test\Acceptance\Attribute\InMemoryAttributeRepository;
use Akeneo\Test\Common\Structure\Attribute\Builder;
use Behat\Behat\Context\Context;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 */
final class CreateAttributeContext implements Context
{
    private Builder $attributeBuilder;
    private ValidatorInterface $validator;
    private InMemoryAttributeRepository $attributeRepository;
    private ConstraintViolationsContext $constraintViolationsContext;

    public function __construct(
        ValidatorInterface $validator,
        InMemoryAttributeRepository $attributeRepository,
        ConstraintViolationsContext $constraintViolationsContext
    ) {
        $this->attributeBuilder = new Builder();
        $this->validator = $validator;
        $this->attributeRepository = $attributeRepository;
        $this->constraintViolationsContext = $constraintViolationsContext;
    }

    /**
     * @When I create a table attribute with a valid configuration
     */
    public function iCreateATableAttributeWithAValidConfiguration(): void
    {
        $attribute = $this->attributeBuilder
            ->withCode('table')
            ->withGroupCode('marketing')
            ->withType(AttributeTypes::TABLE)
            ->build();
        $attribute->setRawTableConfiguration([
            [
                'data_type' => 'select',
                'code' => 'ingredients'
            ],
            [
                'data_type' => 'number',
                'code' => 'quantity'
            ],
            [
                'data_type' => 'boolean',
                'code' => 'isAllergenic',
            ],
            [
                'data_type' => 'text',
                'code' => 'comments'
            ],
        ]);
        $this->saveAttribute($attribute);
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
     * @When I create a table attribute with a configuration without column code
     */
    public function iCreateATableAttributeWithAConfigurationWithoutColumnCode(): void
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
            ], [
                'data_type' => 'text',
            ],
        ]);
        $this->saveAttribute($attribute);
    }

    /**
     * @When /^I create a table attribute with a configuration having column code "([^"]*)"$/
     */
    public function iCreateATableAttributeWithAConfigurationHavingColumnCode(string $code): void
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
            ], [
                'data_type' => 'text',
                'code' => $code,
            ]
        ]);
        $this->saveAttribute($attribute);
    }

    /**
     * @When I create a table attribute with a configuration having unknown validation
     */
    public function iCreateATableAttributeWithAConfigurationHavingUnknownValidation()
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
            ], [
                'data_type' => 'text',
                'code' => 'quantity',
                'validations' => [
                    'unknown' => 123
                ],
            ],
        ]);
        $this->saveAttribute($attribute);
    }

    /**
     * @When I create a table attribute with a configuration having invalid max length validation value type
     */
    public function iCreateATableAttributeWithAConfigurationHavingInvalidMaxLengthValidationValueType()
    {
        $attribute = $this->attributeBuilder
            ->withCode('table')
            ->withGroupCode('marketing')
            ->withType(AttributeTypes::TABLE)
            ->build();
        $attribute->setRawTableConfiguration(
            [
                [
                    'data_type' => 'select',
                    'code' => 'ingredients',
                ],
                [
                    'data_type' => 'text',
                    'code' => 'quantity',
                    'validations' => [
                        'max_length' => 'foo bar',
                    ],
                ],
            ]
        );
        $this->saveAttribute($attribute);
    }

    /**
     * @When I create a table attribute with a configuration having invalid validation type
     */
    public function iCreateATableAttributeWithAConfigurationHavingInvalidValidationType()
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
                'validations' => 123
            ], [
                'data_type' => 'text',
                'code' => 'quantity',
            ]
        ]);
        $this->saveAttribute($attribute);
    }

    /**
     * @When I create a text attribute with a table configuration
     */
    public function iCreateATextAttributeWithAConfiguration(): void
    {
        $attribute = $this->attributeBuilder
            ->withCode('name')
            ->withGroupCode('marketing')
            ->withType(AttributeTypes::TEXT)
            ->build();
        $attribute->setRawTableConfiguration(
            [
                [
                    'data_type' => 'select',
                    'code' => 'ingredients',
                ],
                [
                    'data_type' => 'text',
                    'code' => 'quantity',
                ],
            ],
        );
        $this->saveAttribute($attribute);
    }

    /**
     * @When I create a table attribute with a configuration without type
     */
    public function iCreateATableAttributeWithoutType(): void
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
            ],
            [
                'code' => 'quantity',
            ]
        ]);
        $this->saveAttribute($attribute);
    }

    /**
     * @When I create a table attribute with a configuration having unknown type
     */
    public function iCreateATableAttributeHavingUnknownType(): void
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
            ],
            [
                'data_type' => 'unknown',
                'code' => 'quantity',
            ]
        ]);
        $this->saveAttribute($attribute);
    }

    /**
     * @When I create a table attribute with a configuration having invalid type
     */
    public function iCreateATableAttributeHavingInvalidType(): void
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
            ],
            [
                'data_type' => 1,
                'code' => 'quantity',
            ]
        ]);
        $this->saveAttribute($attribute);
    }

    /**
     * @When I create a table attribute with a configuration having invalid column labels format
     */
    public function iCreateATableAttributeWithAConfigurationHavingInvalidColumnLabelsFormat(): void
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
            ], [
                'data_type' => 'text',
                'code' => 'quantity',
                'labels' => 'A label without locale'
            ]
        ]);
        $this->saveAttribute($attribute);
    }

    /**
     * @When I create a table attribute with a configuration having non activated locale
     */
    public function iCreateATableAttributeWithAConfigurationHavingNonActivatedLocale(): void
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
            ], [
                'data_type' => 'text',
                'code' => 'quantity',
                'labels' => [
                    'pt_DTC' => 'a label'
                ]
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
}
