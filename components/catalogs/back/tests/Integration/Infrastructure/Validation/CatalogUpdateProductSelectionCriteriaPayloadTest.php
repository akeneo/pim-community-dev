<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Validation;

use Akeneo\Catalogs\Infrastructure\Validation\CatalogUpdateProductSelectionCriteriaPayload;
use Akeneo\Catalogs\ServiceAPI\Messenger\CommandBus;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Validation\CatalogUpdateProductSelectionCriteriaPayloadValidator
 */
class CatalogUpdateProductSelectionCriteriaPayloadTest extends IntegrationTestCase
{
    private ?ValidatorInterface $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = self::getContainer()->get(ValidatorInterface::class);
        $this->commandBus = self::getContainer()->get(CommandBus::class);

        $this->purgeDataAndLoadMinimalCatalog();
    }

    public function testItValidates(): void
    {
        $violations = $this->validator->validate([
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
        ], new CatalogUpdateProductSelectionCriteriaPayload());

        $this->assertEmpty($violations);
    }

    public function testItReturnsViolationsWhenProductSelectionCriteriaIsAssociativeArray(): void
    {
        $violations = $this->validator->validate([
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
        ], new CatalogUpdateProductSelectionCriteriaPayload());

        $this->assertViolationsListContains($violations, 'Invalid array structure.');
    }

    public function testItReturnsViolationsWhenFieldIsInvalid(): void {
        $violations = $this->validator->validate([
            [
                'field' => 'some_random_field',
                'operator' => '<=',
                'value' => false,
            ],
        ], new CatalogUpdateProductSelectionCriteriaPayload());

        $this->assertViolationsListContains($violations, 'Invalid field value');
    }
}
