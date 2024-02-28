<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\ServiceAPI\Query;

use Akeneo\Catalogs\ServiceAPI\Query\GetProductUuidsQuery;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetProductUuidsQueryValidationTest extends IntegrationTestCase
{
    private ?ValidatorInterface $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = self::getContainer()->get(ValidatorInterface::class);
    }

    /**
     * @dataProvider validations
     */
    public function testItValidatesTheQuery(GetProductUuidsQuery $query, string $error): void
    {
        $violations = $this->validator->validate($query);

        $this->assertViolationsListContains($violations, $error);
    }

    public function validations(): array
    {
        return [
            'catalogId is not empty' => [
                'query' => new GetProductUuidsQuery(''),
                'error' => 'This value should not be blank.',
            ],
            'catalogId is not an uuid' => [
                'query' => new GetProductUuidsQuery('not_an_uuid'),
                'error' => 'This value is not a valid UUID.',
            ],
            'searchAfter is not an uuid' => [
                'query' => new GetProductUuidsQuery('db1079b6-f397-4a6a-bae4-8658e64ad47c', 'not_an_uuid'),
                'error' => 'This value is not a valid UUID.',
            ],
            'limit is negative' => [
                'query' => new GetProductUuidsQuery('db1079b6-f397-4a6a-bae4-8658e64ad47c', null, -1),
                'error' => 'This value should be between 1 and 1000.',
            ],
            'limit is zero' => [
                'query' => new GetProductUuidsQuery('db1079b6-f397-4a6a-bae4-8658e64ad47c', null, 0),
                'error' => 'This value should be between 1 and 1000.',
            ],
            'limit is more than 1000' => [
                'query' => new GetProductUuidsQuery('db1079b6-f397-4a6a-bae4-8658e64ad47c', null, 1001),
                'error' => 'This value should be between 1 and 1000.',
            ],
            'updatedBefore format is not ISO 8601' => [
                'query' => new GetProductUuidsQuery('db1079b6-f397-4a6a-bae4-8658e64ad47c', null, 100, '2022-09-06'),
                'error' => 'ISO 8601 format is required.',
            ],
            'updatedAfter format is not ISO 8601' => [
                'query' => new GetProductUuidsQuery('db1079b6-f397-4a6a-bae4-8658e64ad47c', null, 100, null, '2022-09-06'),
                'error' => 'ISO 8601 format is required.',
            ],
        ];
    }
}
