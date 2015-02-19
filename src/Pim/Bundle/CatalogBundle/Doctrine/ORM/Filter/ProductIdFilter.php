<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter;

use Pim\Bundle\CatalogBundle\Exception\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Query\Filter\FieldFilterInterface;

/**
 * Product id filter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductIdFilter extends AbstractFilter implements FieldFilterInterface
{
    /** @var array */
    protected $supportedFields;

    /**
     * Instanciate the base filter
     *
     * @param array $supportedFields
     * @param array $supportedOperators
     */
    public function __construct(
        array $supportedFields = [],
        array $supportedOperators = []
    ) {
        $this->supportedFields    = $supportedFields;
        $this->supportedOperators = $supportedOperators;
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldFilter($field, $operator, $value, $locale = null, $scope = null, $options = [])
    {
        if (!is_numeric($value) && !is_array($value)) {
            throw InvalidArgumentException::expected($field, 'array or numeric value', 'filter', 'productId', $value);
        }

        $field = current($this->qb->getRootAliases()) . '.' . $field;
        $condition = $this->prepareCriteriaCondition($field, $operator, $value);
        $this->qb->andWhere($condition);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsField($field)
    {
        return in_array($field, $this->supportedFields);
    }
}
