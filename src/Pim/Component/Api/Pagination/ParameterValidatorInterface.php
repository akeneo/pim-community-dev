<?php

namespace Pim\Component\Api\Pagination;

use Pim\Component\Api\Exception\PaginationParametersException;

/**
 * Interface to validate the pagination parameters.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ParameterValidatorInterface
{
    /**
     * Validate pagination parameters.
     *
     * @param array $parameters
     *
     * @throws PaginationParametersException
     */
    public function validate(array $parameters);
}
