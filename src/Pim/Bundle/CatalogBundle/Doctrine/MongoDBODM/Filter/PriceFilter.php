<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter;

use Pim\Bundle\CatalogBundle\Doctrine\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\ProductQueryUtility;
use Pim\Bundle\CatalogBundle\Doctrine\Query\Operators;
use Pim\Bundle\CatalogBundle\Doctrine\Query\AttributeFilterInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;

/**
 * Price filter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PriceFilter extends AbstractFilter implements AttributeFilterInterface
{
    /** @var array */
    protected $supportedAttributes;

    /**
     * Instanciate the filter
     *
     * @param array $supportedAttributes
     * @param array $supportedOperators
     */
    public function __construct(
        array $supportedAttributes = [],
        array $supportedOperators = []
    ) {
        $this->supportedAttributes = $supportedAttributes;
        $this->supportedOperators  = $supportedOperators;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsAttribute(AttributeInterface $attribute)
    {
        return in_array($attribute->getAttributeType(), $this->supportedAttributes);
    }

    /**
     * {@inheritdoc}
     */
    public function addAttributeFilter(AttributeInterface $attribute, $operator, $value, $locale = null, $scope = null)
    {
        if (!is_string($value)) {
            throw InvalidArgumentException::stringExpected($attribute->getCode(), 'filter', 'price');
        }

        list($data, $currency) = explode(' ', $value);
        $data = (float) $data;

        $field = ProductQueryUtility::getNormalizedValueFieldFromAttribute($attribute, $locale, $scope);
        $field = sprintf('%s.%s', ProductQueryUtility::NORMALIZED_FIELD, $field);
        $field = sprintf('%s.%s', $field, $currency);
        $fieldData = sprintf('%s.data', $field);

        $this->applyFilter($operator, $fieldData, $data);

        return $this;
    }

    /**
     * Apply the filter to the query with the given operator
     *
     * @param string $operator
     * @param string $fieldData
     * @param float  $data
     */
    protected function applyFilter($operator, $fieldData, $data)
    {
        switch ($operator) {
            case Operators::LOWER_THAN:
                $this->qb->field($fieldData)->lt($data);
                break;
            case Operators::LOWER_OR_EQUAL_THAN:
                $this->qb->field($fieldData)->lte($data);
                break;
            case Operators::GREATER_THAN:
                $this->qb->field($fieldData)->gt($data);
                break;
            case Operators::GREATER_OR_EQUAL_THAN:
                $this->qb->field($fieldData)->gte($data);
                break;
            case Operators::IS_EMPTY:
                $this->qb->field($fieldData)->equals(null);
                break;
            default:
                $this->qb->field($fieldData)->equals($data);
        }
    }
}
