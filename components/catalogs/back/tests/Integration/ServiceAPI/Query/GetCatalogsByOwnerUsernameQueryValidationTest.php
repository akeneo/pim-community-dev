<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\ServiceAPI\Query;

use Akeneo\Catalogs\ServiceAPI\Query\GetCatalogsByOwnerUsernameQuery;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetCatalogsByOwnerUsernameQueryValidationTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @dataProvider validations
     */
    public function testItValidatesTheQuery(GetCatalogsByOwnerUsernameQuery $query, string $error): void
    {
        $violations = self::getContainer()->get(ValidatorInterface::class)->validate($query);

        $this->assertViolationsListContains($violations, $error);
    }

    public function validations(): array
    {
        return [
            'page is negative' => [
                'query' => new GetCatalogsByOwnerUsernameQuery('willy', -1, 100),
                'error' => 'This value should be positive.',
            ],
            'page is zero' => [
                'query' => new GetCatalogsByOwnerUsernameQuery('willy', 0, 100),
                'error' => 'This value should be positive.',
            ],
            'limit is negative' => [
                'query' => new GetCatalogsByOwnerUsernameQuery('willy', 1, -1),
                'error' => 'This value should be between 1 and 100.',
            ],
            'limit is zero' => [
                'query' => new GetCatalogsByOwnerUsernameQuery('willy', 0, -1),
                'error' => 'This value should be between 1 and 100.',
            ],
            'limit is more than 100' => [
                'query' => new GetCatalogsByOwnerUsernameQuery('willy', 1, 101),
                'error' => 'This value should be between 1 and 100.',
            ],
        ];
    }
}
