<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Pim\Component\Catalog\Query\Filter\FieldFilterInterface;

/**
 * Product id filter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductIdFilter extends AbstractFieldFilter implements FieldFilterInterface
{
    /**
     * @param array $supportedFields
     * @param array $supportedOperators
     */
    public function __construct(
        array $supportedFields = [],
        array $supportedOperators = []
    ) {
        $this->supportedFields = $supportedFields;
        $this->supportedOperators = $supportedOperators;
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldFilter($field, $operator, $value, $locale = null, $scope = null, $options = [])
    {
        if (!is_numeric($value) && !is_array($value)) {
            throw new InvalidPropertyTypeException(
                $field,
                $value,
                static::class,
                sprintf(
                    'Property "%s" expects array or numeric value, "%s" given.',
                    $field,
                    gettype($value)
                ),
                InvalidPropertyTypeException::EXPECTED_CODE
            );
        }

        $field = current($this->qb->getRootAliases()) . '.' . $field;
        $condition = $this->prepareCriteriaCondition($field, $operator, $value);
        $this->qb->andWhere($condition);

        return $this;
    }
}
