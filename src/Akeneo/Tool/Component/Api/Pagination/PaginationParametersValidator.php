<?php

namespace Akeneo\Tool\Component\Api\Pagination;

use Akeneo\Tool\Component\Api\Exception\PaginationParametersException;

/**
 * Validator for the pagination parameters.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PaginationParametersValidator implements ParameterValidatorInterface
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
    public function validate(array $parameters, array $options = [])
    {
        if (!isset($parameters['pagination_type'])) {
            $parameters['pagination_type'] = PaginationTypes::OFFSET;
        }

        switch ($parameters['pagination_type']) {
            case PaginationTypes::SEARCH_AFTER:
                if (isset($options['support_search_after']) && true === $options['support_search_after']) {
                    $this->validateLimit($parameters);
                } else {
                    throw new PaginationParametersException('Pagination type is not supported.');
                }
                break;
            case PaginationTypes::OFFSET:
                $this->validateLimit($parameters);
                $this->validatePage($parameters);
                $this->validateWithCount($parameters);
                break;
            default:
                throw new PaginationParametersException('Pagination type does not exist.');
        }
    }

    /**
     * @param array $parameters
     *
     * @throws PaginationParametersException
     */
    protected function validatePage(array $parameters)
    {
        if (!isset($parameters['page'])) {
            return;
        }

        $page = $parameters['page'];

        if (!$this->isInteger($page) || $page < 1) {
            throw new PaginationParametersException(sprintf('"%s" is not a valid page number.', $page));
        }
    }

    /**
     * @param array $parameters
     *
     * @throws PaginationParametersException
     */
    protected function validateLimit(array $parameters)
    {
        if (!isset($parameters['limit'])) {
            return;
        }

        $limit = $parameters['limit'];

        if (!$this->isInteger($limit) || $limit < 1) {
            throw new PaginationParametersException(sprintf('"%s" is not a valid limit number.', $limit));
        }

        $limitMax = $this->configuration['pagination']['limit_max'];
        if ($limit > $limitMax) {
            throw new PaginationParametersException(sprintf('You cannot request more than %d items.', $limitMax));
        }
    }

    /**
     * @param array $parameters
     *
     * @throws PaginationParametersException
     */
    protected function validateWithCount(array $parameters)
    {
        if (!isset($parameters['with_count'])) {
            return;
        }

        if (!in_array($parameters['with_count'], ['true', 'false'], true)) {
            throw new PaginationParametersException(
                sprintf(
                    'Parameter "with_count" has to be a boolean. Only "true" or "false" allowed, "%s" given.',
                    $parameters['with_count']
                )
            );
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
