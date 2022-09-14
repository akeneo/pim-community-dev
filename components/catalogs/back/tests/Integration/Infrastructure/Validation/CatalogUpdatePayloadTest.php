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

        $this->purgeDataAndLoadMinimalCatalog();

        $this->validator = self::getContainer()->get(ValidatorInterface::class);

        $this->purgeDataAndLoadMinimalCatalog();
    }

    public function testItValidates(): void
    {
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
        ], new CatalogUpdatePayload());

        $this->assertEmpty($violations);
    }

    public function testItReturnsViolationsWithMissingValues(): void
    {
        $violations = $this->validator->validate([], new CatalogUpdatePayload());

        $this->assertViolationsListContains($violations, 'This field is missing.');
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
}
