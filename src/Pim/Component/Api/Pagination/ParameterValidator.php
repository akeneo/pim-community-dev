<?php

namespace Pim\Component\Api\Pagination;

use Pim\Component\Api\Exception\PaginationParametersException;

/**
 * Validator for the pagination parameters.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ParameterValidator implements ParameterValidatorInterface
{
    /** @var array */
    protected $configuration;

    /**
     * @param array $configuration
     */
    public function __construct(array $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * {@inheritdoc}
     */
    public function validate(array $parameters)
    {
        if (!isset($parameters['page'])) {
            throw new PaginationParametersException('Page number is missing.');
        }

        if (!isset($parameters['limit'])) {
            throw new PaginationParametersException('Limit number is missing.');
        }

        $this->validatePage($parameters['page']);

        $this->validateLimit($parameters['limit']);
    }

    /**
     * @param int|string $page
     *
     * @throws PaginationParametersException
     */
    protected function validatePage($page)
    {
        if (!$this->isInteger($page) || $page < 1) {
            throw new PaginationParametersException(sprintf('"%s" is not a valid page number.', $page));
        }
    }

    /**
     * @param int|string $limit
     *
     * @throws PaginationParametersException
     */
    protected function validateLimit($limit)
    {
        if (!$this->isInteger($limit) || $limit < 1) {
            throw new PaginationParametersException(sprintf('"%s" is not a valid limit number.', $limit));
        }

        $limitMax = $this->configuration['pagination']['limit_max'];
        if ($limitMax < $limit) {
            throw new PaginationParametersException(sprintf('You cannot request more than %d items.', $limitMax));
        }
    }

    /**
     * Check that a parameter is an integer.
     * It's more restrictive than is_numeric because it does not accept float values.
     *
     * @param string|int $parameter
     *
     * @return bool
     */
    protected function isInteger($parameter)
    {
        return strval($parameter) === strval(intval($parameter));
    }
}
