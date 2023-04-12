<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\ServiceAPI\Query;

use Akeneo\Catalogs\ServiceAPI\Query\GetCatalogQuery;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetCatalogQueryValidationTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @dataProvider validations
     */
    public function testItValidatesTheQuery(GetCatalogQuery $query, string $error): void
    {
        $violations = self::getContainer()->get(ValidatorInterface::class)->validate($query);

        $this->assertViolationsListContains($violations, $error);
    }

    public function validations(): array
    {
        return [
            'id is not empty' => [
                'query' => new GetCatalogQuery(''),
                'error' => 'This value should not be blank.',
            ],
            'id is an uuid' => [
                'query' => new GetCatalogQuery('not an uuid'),
                'error' => 'This is not a valid UUID.',
            ],
        ];
    }
}
