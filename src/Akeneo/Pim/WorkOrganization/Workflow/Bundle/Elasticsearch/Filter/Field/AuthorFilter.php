<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Elasticsearch\Filter\Field;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Field\AbstractFieldFilter;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FieldFilterInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;

/**
 * Author filter or an Elasticsearch query.
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class AuthorFilter extends AbstractFieldFilter implements FieldFilterInterface
{
    /**
     * @param array $supportedFields
     * @param array $supportedOperators
     */
    public function __construct(array $supportedFields = [], array $supportedOperators = [])
    {
        $this->supportedFields = $supportedFields;
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
