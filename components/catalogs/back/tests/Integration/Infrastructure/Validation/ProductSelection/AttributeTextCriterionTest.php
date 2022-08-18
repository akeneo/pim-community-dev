<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Validation\ProductSelection;

use Akeneo\Catalogs\Infrastructure\Validation\ProductSelection\AttributeCriterion\AttributeTextCriterion;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeTextCriterionTest extends IntegrationTestCase
{
    private ?ValidatorInterface $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = self::getContainer()->get(ValidatorInterface::class);

        $this->purgeDataAndLoadMinimalCatalog();
    }

    /**
     * @dataProvider validCriterionDataProvider
     */
    public function testItValidates(array $attribute, array $criterion): void
    {
        $this->createAttribute($attribute);

        $violations = $this->validator->validate(
            $criterion,
            new AttributeTextCriterion(),
        );

        $this->assertEmpty($violations);
    }

    public function validCriterionDataProvider(): array
    {
        return [
            'localizable and scopable attribute' => [
                'attribute' => [
                    'code' => 'name',
                    'type' => 'pim_catalog_text',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => true,
                ],
                'criterion' => [
                    'field' => 'name',
                    'operator' => '=',
                    'value' => 'blue',
                    'scope' => 'ecommerce',
                    'locale' => 'en_US',
                ],
            ],
            'scopable attribute' => [
                'attribute' => [
                    'code' => 'name',
                    'type' => 'pim_catalog_text',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => false,
                ],
                'criterion' => [
                    'field' => 'name',
                    'operator' => '=',
                    'value' => 'blue',
                    'scope' => 'ecommerce',
                    'locale' => null,
                ],
            ],
            'localizable attribute' => [
                'attribute' => [
                    'code' => 'name',
                    'type' => 'pim_catalog_text',
                    'group' => 'other',
                    'scopable' => false,
                    'localizable' => true,
                ],
                'criterion' => [
                    'field' => 'name',
                    'operator' => '=',
                    'value' => 'blue',
                    'scope' => null,
                    'locale' => 'en_US',
                ],
            ],
            'non localizable and non scopable attribute' => [
                'attribute' => [
                    'code' => 'name',
                    'type' => 'pim_catalog_text',
                    'group' => 'other',
                    'scopable' => false,
                    'localizable' => false,
                ],
                'criterion' => [
                    'field' => 'name',
                    'operator' => '=',
                    'value' => 'blue',
                    'scope' => null,
                    'locale' => null,
                ],
            ],
        ];
    }

    /**
     * @dataProvider invalidStructureDataProvider
     */
    public function testItReturnsViolationsWhenAttributeTextCriterionStructureIsInvalid(
        array $criterion,
        string $expectedMessage
    ): void {
        $violations = $this->validator->validate(
            $criterion,
            new AttributeTextCriterion(),
        );

        $this->assertViolationsListContains($violations, $expectedMessage);
    }

    public function invalidStructureDataProvider(): array
    {
        return [
            'invalid field value' => [
                'criterion' => [
                    'field' => 42,
                    'operator' => '=',
                    'value' => 'blue',
                    'scope' => 'ecommerce',
                    'locale' => 'en_US',
                ],
                'expectedMessage' => 'This value should be of type string.',
            ],
            'invalid operator' => [
                'criterion' => [
                    'field' => 'name',
                    'operator' => 'IN',
                    'value' => 'blue',
                    'scope' => 'ecommerce',
                    'locale' => 'en_US',
                ],
                'expectedMessage' => 'The value you selected is not a valid choice.',
            ],
            'invalid value' => [
                'criterion' => [
                    'field' => 'name',
                    'operator' => '=',
                    'value' => 42,
                    'scope' => 'ecommerce',
                    'locale' => 'en_US',
                ],
                'expectedMessage' => 'This value should be of type string.',
            ],
            'invalid scope' => [
                'criterion' => [
                    'field' => 'name',
                    'operator' => '=',
                    'value' => 'blue',
                    'scope' => 42,
                    'locale' => 'en_US',
                ],
                'expectedMessage' => 'This value should be of type string.',
            ],
            'invalid locale' => [
                'criterion' => [
                    'field' => 'name',
                    'operator' => '=',
                    'value' => 'blue',
                    'scope' => 'ecommerce',
                    'locale' => 42,
                ],
                'expectedMessage' => 'This value should be of type string.',
            ],
        ];
    }

    /**
     * @dataProvider invalidValuesDataProvider
     */
    public function testItReturnsViolationsWhenAttributeTextCriterionValuesAreInvalid(
        array $attribute,
        array $criterion,
        string $expectedMessage
    ): void {
        $this->createAttribute($attribute);

        $violations = $this->validator->validate(
            $criterion,
            new AttributeTextCriterion(),
        );

        $this->assertViolationsListContains($violations, $expectedMessage);
    }

    public function invalidValuesDataProvider(): array
    {
        return [
            'field with invalid locale for a channel' => [
                'attribute' => [
                    'code' => 'name',
                    'type' => 'pim_catalog_text',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => true,
                ],
                'criterion' => [
                    'field' => 'name',
                    'operator' => '=',
                    'value' => 'blue',
                    'scope' => 'ecommerce',
                    'locale' => 'kz_KZ',
                ],
                'expectedMessage' => 'This locale is disabled for this channel. Please check your channel settings or remove this criterion.',
            ],
            'field with invalid scope' => [
                'attribute' => [
                    'code' => 'name',
                    'type' => 'pim_catalog_text',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => false,
                ],
                'criterion' => [
                    'field' => 'name',
                    'operator' => '=',
                    'value' => 'blue',
                    'scope' => 'unknown_scope',
                    'locale' => null,
                ],
                'expectedMessage' => 'This channel has been deactivated. Please check your channel settings or remove this criterion.',
            ],
            'field with invalid locale' => [
                'attribute' => [
                    'code' => 'name',
                    'type' => 'pim_catalog_text',
                    'group' => 'other',
                    'scopable' => false,
                    'localizable' => true,
                ],
                'criterion' => [
                    'field' => 'name',
                    'operator' => '=',
                    'value' => 'blue',
                    'scope' => null,
                    'locale' => 'kz_KZ',
                ],
                'expectedMessage' => 'This locale does not exist.',
            ],
            'field with EMPTY operator has a non empty value' => [
                'attribute' => [
                    'code' => 'name',
                    'type' => 'pim_catalog_text',
                    'group' => 'other',
                    'scopable' => 'ecommerce',
                    'localizable' => 'en_US',
                ],
                'criterion' => [
                    'field' => 'name',
                    'operator' => 'EMPTY',
                    'value' => 'blue',
                    'scope' => 'ecommerce',
                    'locale' => 'en_US',
                ],
                'expectedMessage' => 'This value must be empty.',
            ],
            'field with CONTAINS operator has an empty value' => [
                'attribute' => [
                    'code' => 'name',
                    'type' => 'pim_catalog_text',
                    'group' => 'other',
                    'scopable' => 'ecommerce',
                    'localizable' => 'en_US',
                ],
                'criterion' => [
                    'field' => 'name',
                    'operator' => 'CONTAINS',
                    'value' => '',
                    'scope' => 'ecommerce',
                    'locale' => 'en_US',
                ],
                'expectedMessage' => 'This value must not be empty.',
            ],
        ];
    }
}
