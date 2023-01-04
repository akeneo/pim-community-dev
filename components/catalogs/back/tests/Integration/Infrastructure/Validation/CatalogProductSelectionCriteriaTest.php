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
    private ?ValidatorInterface $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = self::getContainer()->get(ValidatorInterface::class);

        $this->purgeDataAndLoadMinimalCatalog();
    }

    public function testItValidates(): void
    {
        $violations = $this->validator->validate(
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
            )
        );

        $this->assertEmpty($violations);
    }

    public function testItReturnsViolationsWhenProductSelectionCriteriaIsAssociativeArray(): void
    {
        $violations = $this->validator->validate(
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
                    ]
                ],
                [],
                [],
            )
        );

        $this->assertViolationsListContains($violations, 'Invalid array structure.');
    }

    public function testItReturnsViolationsWhenFieldIsInvalid(): void
    {
        $violations = $this->validator->validate(
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
            )
        );

        $this->assertViolationsListContains($violations, 'Invalid field value');
    }
}
