<?php

declare(strict_types=1);

namespace Specification\Akeneo\Category\Infrastructure\Handler;

use Akeneo\Category\Application\Query\ExternalApiSqlParameters;
use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\Domain\Query\GetCategoryInterface;
use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Domain\ValueObject\Code;
use Akeneo\Category\Domain\ValueObject\Position;
use Akeneo\Category\Infrastructure\Validation\ExternalApiSearchFiltersValidator;
use Doctrine\DBAL\Connection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SearchFiltersSqlSpec extends ObjectBehavior
{
    function let(
        ExternalApiSearchFiltersValidator $searchFiltersValidator,
        GetCategoryInterface $getCategory,
    ) {
        $this->beConstructedWith(
            $searchFiltersValidator,
            $getCategory,
        );
    }

    function it_generates_correct_sqlWhere_for_parent_filter(
        ExternalApiSearchFiltersValidator $searchFiltersValidator,
        GetCategoryInterface $getCategory,
    )
    {
        $value = '3';
        $searchFilters = [
            'parent' => [
                [
                    'operator' => '=',
                    'value' => $value,
                ]
            ],
        ];

        $category = new Category(
            id: new CategoryId(1),
            code: new Code('test'),
            templateUuid: null,
            rootId: new CategoryId(4),
            position: new Position(1, 3, 0),
        );
        $searchFiltersValidator->validate(Argument::any())->shouldBeCalledOnce();
        $getCategory->byCode(Argument::any())->willReturn($category);

        $params = [
            'left' => $category->getPosition()->left,
            'right' => $category->getPosition()->right,
            'root' => $category->getRootId()->getValue(),
        ];
        $types = [
            'left' => \PDO::PARAM_INT,
            'right' => \PDO::PARAM_INT,
            'root' => \PDO::PARAM_INT,
        ];
        $expected = new ExternalApiSqlParameters(
            sqlWhere: 'category.lft > :left AND category.rgt < :right AND category.root = :root',
            params: $params,
            types: $types,
            limitAndOffset: null,
        );
        $this->build($searchFilters)->shouldBeLike($expected);
    }

    function it_generates_correct_sqlWhere_for_root_filter_set_to_true(
        ExternalApiSearchFiltersValidator $searchFiltersValidator,
    )
    {
        $value =  true;
        $searchFilters = [
            'is_root' => [
                [
                    'operator' => '=',
                    'value' => $value,
                ]
            ],
        ];

        $searchFiltersValidator->validate(Argument::any())->shouldBeCalledOnce();
        $expected = new ExternalApiSqlParameters(
            sqlWhere: 'category.parent_id IS NULL',
            params: null,
            types: null,
            limitAndOffset: null,
        );
        $this->build($searchFilters)->shouldBeLike($expected);
    }

    function it_generates_correct_sqlWhere_for_root_filter_set_to_false(
        ExternalApiSearchFiltersValidator $searchFiltersValidator,
    )
    {
        $value =  false;
        $searchFilters = [
            'is_root' => [
                [
                    'operator' => '=',
                    'value' => $value,
                ]
            ],
        ];

        $searchFiltersValidator->validate(Argument::any())->shouldBeCalledOnce();
        $expected = new ExternalApiSqlParameters(
            sqlWhere: 'category.parent_id IS NOT NULL',
            params: null,
            types: null,
            limitAndOffset: null,
        );
        $this->build($searchFilters)->shouldBeLike($expected);
    }

    function it_generates_correct_sqlWhere_for_parent_and_is_root_filters(
        ExternalApiSearchFiltersValidator $searchFiltersValidator,
        GetCategoryInterface $getCategory,
    )
    {
        $parentValue = '3';
        $isRootValue =  true;
        $searchFilters = [
            'parent' => [
                [
                    'operator' => '=',
                    'value' => $parentValue,
                ]
            ],
            'is_root' => [
                [
                    'operator' => '=',
                    'value' => $isRootValue,
                ]
            ],
        ];

        $category = new Category(
            id: new CategoryId(1),
            code: new Code('test'),
            templateUuid: null,
            rootId: new CategoryId(4),
            position: new Position(1, 3, 0),
        );
        $searchFiltersValidator->validate(Argument::any())->shouldBeCalledOnce();
        $getCategory->byCode(Argument::any())->willReturn($category);

        $params = [
            'left' => $category->getPosition()->left,
            'right' => $category->getPosition()->right,
            'root' => $category->getRootId()->getValue(),
        ];
        $types = [
            'left' => \PDO::PARAM_INT,
            'right' => \PDO::PARAM_INT,
            'root' => \PDO::PARAM_INT,
        ];
        $expected = new ExternalApiSqlParameters(
            sqlWhere: 'category.lft > :left AND category.rgt < :right AND category.root = :root AND category.parent_id IS NULL',
            params: $params,
            types: $types,
            limitAndOffset: null,
        );
        $this->build($searchFilters)->shouldBeLike($expected);
    }

    function it_generates_correct_sqlWhere_for_category_codes_filter(
        ExternalApiSearchFiltersValidator $searchFiltersValidator,
    )
    {
        $values = ['master', 'category1'];
        $searchFilters = [
            'code' => [
                [
                    'operator' => 'IN',
                    'value' => $values,
                ]
            ],
        ];

        $searchFiltersValidator->validate(Argument::any())->shouldBeCalledOnce();

        $expected = new ExternalApiSqlParameters(
            sqlWhere: 'category.code IN (:code_0)',
            params: [
                'code_0' => [
                    'master',
                    'category1',
                ]
            ],
            types: [
                'code_0' => Connection::PARAM_STR_ARRAY,
            ],
            limitAndOffset: null,
        );
        $this->build($searchFilters)->shouldBeLike($expected);
    }

    function it_generates_correct_sqlWhere_for_greater_than_date_filter(
        ExternalApiSearchFiltersValidator $searchFiltersValidator,
    )
    {
        $value = '2019-06-09T12:00:00+00:00';
        $searchFilters = [
            'updated' => [
                [
                    'operator' => '>',
                    'value' => $value,
                ]
            ],
        ];

        $searchFiltersValidator->validate(Argument::any())->shouldBeCalledOnce();

        $params = [
            'updated_0' => $value,
        ];
        $types = [
            'updated_0' => \PDO::PARAM_STR,
        ];
        $expected = new ExternalApiSqlParameters(
            sqlWhere: 'category.updated > :updated_0',
            params: $params,
            types: $types,
            limitAndOffset: null,
        );
        $this->build($searchFilters)->shouldBeLike($expected);
    }

    function it_throws_exception_on_bad_operator(
        ExternalApiSearchFiltersValidator $searchFiltersValidator,
    )
    {
        $value = '2019-06-09T12:00:00+00:00';
        $searchFilters = [
            'updated' => [
                [
                    'operator' => '!=',
                    'value' => $value,
                ]
            ],
        ];

        $searchFiltersValidator->validate(Argument::any())->shouldBeCalledOnce();
        $this->shouldThrow(\InvalidArgumentException::class)->duringbuild($searchFilters);
    }
}
