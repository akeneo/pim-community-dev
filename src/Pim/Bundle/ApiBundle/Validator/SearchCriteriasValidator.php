<?php

declare(strict_types=1);

namespace Pim\Bundle\ApiBundle\Validator;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SearchCriteriasValidator
{
    /**
     * Prepares criterias from search parameters
     * It throws exceptions if search parameters are not correctly filled
     *
     * @param string $searchString
     *
     * @throws UnprocessableEntityHttpException
     * @throws BadRequestHttpException
     *
     * @return array
     */
    public function validate(string $searchString): array
    {
        $searchParameters = json_decode($searchString, true);

        if (null === $searchParameters) {
            throw new BadRequestHttpException('Search query parameter should be valid JSON.');
        }

        if (!is_array($searchParameters)) {
            throw new UnprocessableEntityHttpException(
                sprintf('Search query parameter has to be an array, "%s" given.', gettype($searchParameters))
            );
        }

        foreach ($searchParameters as $searchKey => $searchParameter) {
            if (!is_array($searchParameters) || !isset($searchParameter[0])) {
                throw new UnprocessableEntityHttpException(
                    sprintf(
                        'Structure of filter "%s" should respect this structure: %s.',
                        $searchKey,
                        sprintf('{"%s":[{"operator": "my_operator", "value": "my_value"}]}', $searchKey)
                    )
                );
            }

            foreach ($searchParameter as $searchOperator) {
                if (!isset($searchOperator['operator'])) {
                    throw new UnprocessableEntityHttpException(
                        sprintf('Operator is missing for the property "%s".', $searchKey)
                    );
                }

                if (!isset($searchOperator['value'])) {
                    throw new UnprocessableEntityHttpException(
                        sprintf('Value is missing for the property "%s".', $searchKey)
                    );
                }
            }
        }

        return $searchParameters;
    }
}
