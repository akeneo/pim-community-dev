<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\Validator;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * @author    Thomas Fehringer <thomas.fehringer@getakeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ValidateIdentifiersLimit
{
    private const LIMIT = 100;

    /**
     * @throws BadRequestException
     */
    public function validate(array $search): void
    {
        $identifiersQuery = $search['identifier'] ?? null;

        if (!$identifiersQuery) {
            return;
        }

        $inQuery = current(array_filter($identifiersQuery, static fn ($query) => $query['operator'] === Operators::IN_LIST));

        if (!$inQuery) {
            return;
        }

        if ($inQuery['value'] === null) {
            throw new UnprocessableEntityHttpException("The identifier filter can't contain null value");
        }

        if (!\is_array($inQuery['value'])) {
            throw new UnprocessableEntityHttpException('The identifier filter value should be an array');
        }

        if (count(array_unique($inQuery['value'])) > self::LIMIT) {
            throw new BadRequestException(
                "The identifier filter can't contain more than ". self::LIMIT ." product identifiers."
            );
        }
    }
}
