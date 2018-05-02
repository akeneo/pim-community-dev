<?php

namespace Akeneo\Tool\Component\Api\Pagination;

use Akeneo\Tool\Component\Api\Exception\PaginationParametersException;

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
     * @param array $parameters parameters of the pagination
     * @param array $options    options for the validation of the parameters
     *
     * @throws PaginationParametersException
     */
    public function validate(array $parameters, array $options = []);
}
