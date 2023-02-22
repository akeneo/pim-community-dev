<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Validation;

use Akeneo\Catalogs\Infrastructure\Validation\CatalogUpdatePayload;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Validation\CatalogUpdatePayload
 */
class CatalogUpdatePayloadTest extends IntegrationTestCase
{
    private ?ValidatorInterface $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = self::getContainer()->get(ValidatorInterface::class);

        $this->purgeData();
    }

    public function testItValidates(): void
    {
        $this->purgeDataAndLoadMinimalCatalog();

        $violations = $this->validator->validate([
            'enabled' => false,
            'product_selection_criteria' => [
                [
                    'field' => 'enabled',
                    'operator' => '=',
                    'value' => true,
                ],
                [
                    'field' => 'completeness',
                    'operator' => '>',
                    'value' => 80,
                    'scope' => 'ecommerce',
                    'locale' => 'en_US',
                ],
                [
                    'field' => 'categories',
                    'operator' => 'IN',
                    'value' => ['master'],
                ],
                [
                    'field' => 'categories',
                    'operator' => 'UNCLASSIFIED',
                    'value' => [],
                ],
            ],
            'product_value_filters' => [
                'channels' => ['ecommerce'],
                'locales' => ['en_US'],
                'currencies' => ['EUR', 'USD'],
            ],
            'product_mapping' => [
                'Product uuid' => [
                    'source' => 'uuid',
                    'scope' => null,
                    'locale' => null,
                ],
            ],
        ], new CatalogUpdatePayload());

        $this->assertEmpty($violations);
    }

    public function testItReturnsViolationsWithMissingValues(): void
    {
        $violations = $this->validator->validate([], new CatalogUpdatePayload());

        $this->assertViolationsListContains($violations, 'This field is missing.');
    }

    /**
     * @dataProvider numberOfProductSelectionCriteriaDataProvider
     */
    public function testItValidatesTheNumberOfProductSelectionCriteria(int $number, bool $allowed): void
    {
        $violations = $this->validator->validate([
            'enabled' => true,
            'product_selection_criteria' => \array_map(fn (): array => [
                'field' => 'enabled',
                'operator' => '=',
                'value' => true,
            ], \range(1, $number)),
            'product_value_filters' => [],
            'product_mapping' => [],
        ], new CatalogUpdatePayload());

        if ($allowed) {
            $this->assertEmpty($violations);
        } else {
            $this->assertViolationsListContains($violations, 'Too many criteria.');
        }
    }

    public function numberOfProductSelectionCriteriaDataProvider(): array
    {
        return [
            'valid with 25 product selection criteria' => [
                'number' => 25,
                'allowed' => true,
            ],
            'not valid with 26 product selection criteria' => [
                'number' => 26,
                'allowed' => false,
            ],
        ];
    }

    public function testItReturnsViolationsWhenProductSelectionCriteriaIsAssociativeArray(): void
    {
        $violations = $this->validator->validate([
            'enabled' => true,
            'product_selection_criteria' => [
                'foo' => [
                    'field' => 'enabled',
                    'operator' => '=',
                    'value' => true,
                ],
                [
                    'field' => 'family',
                    'operator' => 'EMPTY',
                    'value' => [],
                ],
                [
                    'field' => 'completeness',
                    'operator' => '>',
                    'value' => 80,
                    'scope' => 'ecommerce',
                    'locale' => 'en_US',
                ]
            ],
            'product_value_filters' => [],
            'product_mapping' => [],
        ], new CatalogUpdatePayload());

        $this->assertViolationsListContains($violations, 'Invalid array structure.');
    }

    /**
     * @dataProvider invalidFieldDataProvider
     */
    public function testItReturnsViolationsWhenProductSelectionCriterionIsInvalid(
        array $criterion,
        string $expectedMessage
    ): void {
        $violations = $this->validator->validate([
            'enabled' => false,
            'product_selection_criteria' => [$criterion],
            'product_value_filters' => [],
            'product_mapping' => [],
        ], new CatalogUpdatePayload());

        $this->assertViolationsListContains($violations, $expectedMessage);
    }

    public function invalidFieldDataProvider(): array
    {
        return [
            'invalid field value' => [
                'criterion' => [
                    'field' => 'some_random_field',
                    'operator' => '<=',
                    'value' => false,
                ],
                'expectedMessage' => 'Invalid field value',
            ],
        ];
    }

    /**
     * @dataProvider invalidProductValueFiltersProvider
     */
    public function testItReturnsViolationsWhenProductValueFiltersAreInvalid(
        array $filters,
        string $expectedMessage
    ): void {
        $violations = $this->validator->validate([
            'enabled' => false,
            'product_selection_criteria' => [],
            'product_value_filters' => $filters,
            'product_mapping' => [],
        ], new CatalogUpdatePayload());

        $this->assertViolationsListContains($violations, $expectedMessage);
    }

    public function invalidProductValueFiltersProvider(): array
    {
        return [
            'channel is not a valid array' => [
                'filters' => ['channels' => 'ecommerce'],
                'expectedMessage' => 'This value should be of type array.',
            ],
            'channel does not exist' => [
                'filters' => ['channels' => ['removed_channel']],
                'expectedMessage' => 'The channel "removed_channel" has been deactivated. Please check your channel settings or remove this filter.',
            ],
            'locale is not a valid array' => [
                'filters' => ['locales' => 'en_US'],
                'expectedMessage' => 'This value should be of type array.',
            ],
            'locale is not activated' => [
                'filters' => ['locales' => ['removed_locale']],
                'expectedMessage' => 'The locale "removed_locale" has been deactivated. Please check your locale settings or remove this filter.',
            ],
            'currency is not a valid array' => [
                'filters' => ['currencies' => 'USD'],
                'expectedMessage' => 'This value should be of type array.',
            ],
            'currency is not activated' => [
                'filters' => ['currencies' => ['AUD']],
                'expectedMessage' => 'The currency "AUD" has been deactivated. Please check your currencies settings or remove this filter.',
            ],
        ];
    }

    public function testItReturnsViolationsWhenProductMappingIsNotAssociativeArray(): void
    {
        $violations = $this->validator->validate([
            'enabled' => true,
            'product_selection_criteria' => [],
            'product_value_filters' => [],
            'product_mapping' => [
                [
                    'source' => 'uuid',
                    'scope' => null,
                    'locale' => null,
                ],
            ],
        ], new CatalogUpdatePayload());

        $this->assertViolationsListContains($violations, 'Invalid array structure.');
    }

    /**
     * @dataProvider invalidSourceDataProvider
     */
    public function testItReturnsViolationsWhenProductMappingIsInvalid(
        array $source,
        string $expectedMessage
    ): void {
        $violations = $this->validator->validate([
            'enabled' => false,
            'product_selection_criteria' => [],
            'product_value_filters' => [],
            'product_mapping' => [$source],
        ], new CatalogUpdatePayload());

        $this->assertViolationsListContains($violations, $expectedMessage);
    }

    public function invalidSourceDataProvider(): array
    {
        return [
            'invalid source value' => [
                'source' => [
                    'source' => 'unknown_attribute',
                    'scope' => null,
                    'locale' => null,
                ],
                'expectedMessage' => 'This attribute has been deleted.',
            ],
        ];
    }
}
