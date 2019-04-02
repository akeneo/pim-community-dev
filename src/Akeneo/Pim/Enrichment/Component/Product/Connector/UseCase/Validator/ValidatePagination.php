<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\Validator;

use Akeneo\Tool\Component\Api\Exception\InvalidQueryException;
use Akeneo\Tool\Component\Api\Exception\PaginationParametersException;
use Akeneo\Tool\Component\Api\Pagination\PaginationParametersValidator;

/**
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @author    Mathias Métayer <mathias.metayer@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ValidatePagination
{
    /** @var PaginationParametersValidator */
    private $paginationParametersValidator;

    public function __construct(
        PaginationParametersValidator $paginationParametersValidator
    ) {
        $this->paginationParametersValidator = $paginationParametersValidator;
    }

    /**
     * @throws InvalidQueryException
     */
    public function validate(string $paginationType, $page, $limit, string $withCount): void
    {
        $parameters = [
            'pagination_type' => $paginationType,
            'limit' => $limit,
            'page' => $page,
            'with_count' => $withCount
        ];

        try {
            $this->paginationParametersValidator->validate($parameters, ['support_search_after' => true]);
        } catch (PaginationParametersException $e) {
            throw new InvalidQueryException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
