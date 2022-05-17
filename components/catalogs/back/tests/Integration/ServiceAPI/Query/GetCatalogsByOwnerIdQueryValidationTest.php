<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\ServiceAPI\Query;

use Akeneo\Catalogs\ServiceAPI\Query\GetCatalogsByOwnerIdQuery;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetCatalogsByOwnerIdQueryValidationTest extends IntegrationTestCase
{
    private ?ValidatorInterface $validator;

    public function setUp(): void
    {
        parent::setUp();

        $this->validator = self::getContainer()->get(ValidatorInterface::class);
    }

    /**
     * @dataProvider validations
     */
    public function testItValidatesTheQuery(GetCatalogsByOwnerIdQuery $query, string $error): void
    {
        $violations = $this->validator->validate($query);

        $this->assertViolationsListContains($violations, $error);
    }

    public function validations(): array
    {
        return [
            'offset is negative' => [
                'query' => new GetCatalogsByOwnerIdQuery(123, -1, 100),
                'error' => 'This value should be either positive or zero.',
            ],
            'limit is negative' => [
                'query' => new GetCatalogsByOwnerIdQuery(123, 0, -1),
                'error' => 'This value should be either positive or zero.',
            ],
        ];
    }
}
