<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\Validator;

use Akeneo\Tool\Component\Api\Exception\InvalidQueryException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @author    Mathias Métayer <mathias.metayer@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ValidateCriterion
{
    /**
     * @throws InvalidQueryException
     */
    public function validate(?array $search): void
    {
        foreach ($search as $searchKey => $filters) {
            if (!is_array($filters) || !isset($filters[0])) {
                throw new InvalidQueryException(
                    sprintf(
                        'Structure of filter "%s" should respect this structure: %s',
                        $searchKey,
                        sprintf('{"%s":[{"operator": "my_operator", "value": "my_value"}]}', $searchKey)
                    )
                );
            }

            foreach ($filters as $searchFilter) {
                if (!isset($searchFilter['operator'])) {
                    throw new InvalidQueryException(
                        sprintf('Operator is missing for the property "%s".', $searchKey)
                    );
                }

                if (!is_string($searchFilter['operator'])) {
                    throw new InvalidQueryException(
                        sprintf('Operator has to be a string, "%s" given.', gettype($searchFilter['operator']))
                    );
                }
            }
        }
    }
}
