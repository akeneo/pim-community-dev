<?php

declare(strict_types=1);

namespace Akeneo\Category\back\tests\Integration\Infrastructure\Storage\Sql;

use Akeneo\Category\Application\Query\GetCategoriesParametersBuilder;
use Akeneo\Category\back\tests\Integration\Helper\CategoryTestCase;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class GetCategoriesParametersBuilderSqlIntegration extends CategoryTestCase
{
    public function testBuildParameters(): void
    {
        $expectedParameters = [
            'sqlWhere' => 'category.code IN (:category_codes)',
            'sqlLimitOffset' => 'LIMIT 10 OFFSET 3',
            'params' => [
                'category_codes' => ['sock'],
                'with_enriched_attributes' => true
            ],
            'types' => [
                'category_codes' => Connection::PARAM_STR_ARRAY,
                'with_enriched_attributes' => \PDO::PARAM_BOOL
            ]
        ];

        $parameters = $this->get(GetCategoriesParametersBuilder::class)->build(
            categoryCodes: ['sock'],
            limit: 10,
            offset: 3,
            isEnrichedAttributes: true
        );

        $this->assertEquals($expectedParameters, $parameters);
    }

    public function testBuildParametersWithNoOffset(): void
    {
        $expectedParameters = [
            'sqlWhere' => 'category.code IN (:category_codes)',
            'sqlLimitOffset' => 'LIMIT 10',
            'params' => [
                'category_codes' => ['sock'],
                'with_enriched_attributes' => true
            ],
            'types' => [
                'category_codes' => Connection::PARAM_STR_ARRAY,
                'with_enriched_attributes' => \PDO::PARAM_BOOL
            ]
        ];

        $parameters = $this->get(GetCategoriesParametersBuilder::class)->build(
            categoryCodes: ['sock'],
            limit: 10,
            offset: 0,
            isEnrichedAttributes: true
        );

        $this->assertEquals($expectedParameters, $parameters);
    }

    public function testBuildParametersWithNoCategoryCodes(): void
    {
        $expectedParameters = [
            'sqlWhere' => '1=1',
            'sqlLimitOffset' => 'LIMIT 10 OFFSET 3',
            'params' => [
                'category_codes' => [],
                'with_enriched_attributes' => true
            ],
            'types' => [
                'category_codes' => Connection::PARAM_STR_ARRAY,
                'with_enriched_attributes' => \PDO::PARAM_BOOL
            ]
        ];

        $parameters = $this->get(GetCategoriesParametersBuilder::class)->build(
            categoryCodes: [],
            limit: 10,
            offset: 3,
            isEnrichedAttributes: true
        );

        $this->assertEquals($expectedParameters, $parameters);
    }

    public function testBuildParametersWithNoEnrichedAttributes(): void
    {
        $expectedParameters = [
            'sqlWhere' => 'category.code IN (:category_codes)',
            'sqlLimitOffset' => 'LIMIT 10 OFFSET 3',
            'params' => [
                'category_codes' => ['sock'],
                'with_enriched_attributes' => false
            ],
            'types' => [
                'category_codes' => Connection::PARAM_STR_ARRAY,
                'with_enriched_attributes' => \PDO::PARAM_BOOL
            ]
        ];

        $parameters = $this->get(GetCategoriesParametersBuilder::class)->build(
            categoryCodes: ['sock'],
            limit: 10,
            offset: 3,
            isEnrichedAttributes: false
        );

        $this->assertEquals($expectedParameters, $parameters);
    }
}
