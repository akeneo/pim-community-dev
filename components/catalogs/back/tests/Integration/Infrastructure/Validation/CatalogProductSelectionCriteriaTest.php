<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Validation;

use Akeneo\Catalogs\Domain\Catalog;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Validation\CatalogProductSelectionCriteriaValidator
 */
class CatalogProductSelectionCriteriaTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();
    }

    public function testItValidates(): void
    {
        $violations = self::getContainer()->get(ValidatorInterface::class)->validate(
            new Catalog(
                'db1079b6-f397-4a6a-bae4-8658e64ad47c',
                'Store US',
                'willy',
                false,
                [
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
                [],
                [],
            ),
        );

        $this->assertEmpty($violations);
    }

    public function testItReturnsViolationsWhenProductSelectionCriteriaIsAssociativeArray(): void
    {
        $violations = self::getContainer()->get(ValidatorInterface::class)->validate(
            new Catalog(
                'db1079b6-f397-4a6a-bae4-8658e64ad47c',
                'Store US',
                'willy',
                false,
                [
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
                    ],
                ],
                [],
                [],
            ),
        );

        $this->assertViolationsListContains($violations, 'Invalid array structure.');
    }

    public function testItReturnsViolationsWhenFieldIsInvalid(): void
    {
        $violations = self::getContainer()->get(ValidatorInterface::class)->validate(
            new Catalog(
                'db1079b6-f397-4a6a-bae4-8658e64ad47c',
                'Store US',
                'willy',
                false,
                [
                    [
                        'field' => 'some_random_field',
                        'operator' => '<=',
                        'value' => false,
                    ],
                ],
                [],
                [],
            ),
        );

        $this->assertViolationsListContains($violations, 'Invalid field value');
    }

    /**
     * @dataProvider numberOfProductSelectionCriteriaDataProvider
     */
    public function testItValidatesTheNumberOfProductSelectionCriteria(int $number, bool $allowed): void
    {
        $violations = self::getContainer()->get(ValidatorInterface::class)->validate(
            new Catalog(
                'db1079b6-f397-4a6a-bae4-8658e64ad47c',
                'Store US',
                'willy',
                false,
                \array_map(
                    fn (): array => [
                        'field' => 'enabled',
                        'operator' => '=',
                        'value' => true,
                    ],
                    \range(1, $number),
                ),
                [],
                [],
            ),
        );

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
}
