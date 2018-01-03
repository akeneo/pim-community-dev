<?php

namespace Pim\Bundle\CatalogBundle\Elasticsearch\Filter\Attribute;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Pim\Bundle\CatalogBundle\Elasticsearch\Filter\Field\AbstractFieldFilter;
use Pim\Component\Catalog\Query\Filter\FieldFilterInterface;

/**
 * Author filter or an Elasticsearch query.
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AuthorFilter extends AbstractFieldFilter implements FieldFilterInterface
{
    /**
     * @param array $supportedOperators
     */
    public function __construct(array $supportedOperators = [])
    {
        $this->supportedOperators = $supportedOperators;
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldFilter($field, $operator, $value, $locale = null, $channel = null, $options = [])
    {
        if (null === $this->searchQueryBuilder) {
            throw new \LogicException('The search query builder is not initialized in the filter.');
        }

        $this->checkValue($value);

        $clause = [
            'terms' => [
                'author' => $value,
            ],
        ];

        $this->searchQueryBuilder->addFilter($clause);

        return $this;
    }

    /**
     * Check if the value is valid
     *
     * @param mixed $value
     */
    protected function checkValue($value)
    {
        if (!is_array($value)) {
            throw InvalidPropertyTypeException::arrayExpected('author', static::class, $value);
        }
    }
}
