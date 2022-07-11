<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Validation;

use Akeneo\Catalogs\Infrastructure\Validation\UpdateCatalogPayloadIsValid;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Validation\UpdateCatalogPayloadIsValid
 */
class UpdateCatalogPayloadIsValidTest extends IntegrationTestCase
{
    private ?ValidatorInterface $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = self::getContainer()->get(ValidatorInterface::class);
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
            ],
        ], new UpdateCatalogPayloadIsValid());

        $this->assertEmpty($violations);
    }

    public function testItReturnsViolationsWithMissingValues(): void
    {
        $violations = $this->validator->validate([], new UpdateCatalogPayloadIsValid());

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
            ],
        ], new UpdateCatalogPayloadIsValid());

        $this->assertViolationsListContains($violations, 'Invalid array structure.');
    }
}
