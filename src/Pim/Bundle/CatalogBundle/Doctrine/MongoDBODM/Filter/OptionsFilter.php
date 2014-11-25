<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter;

use Pim\Bundle\CatalogBundle\Doctrine\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\ProductQueryUtility;
use Pim\Bundle\CatalogBundle\Doctrine\Query\Operators;
use Pim\Bundle\CatalogBundle\Doctrine\Query\AttributeFilterInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;

/**
 * Multi options filter for MongoDB
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OptionsFilter extends AbstractFilter implements AttributeFilterInterface
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
        $this->checkValue($attribute, $operator, $value);

        $field = ProductQueryUtility::getNormalizedValueFieldFromAttribute($attribute, $locale, $scope);
        $field = sprintf('%s.%s', ProductQueryUtility::NORMALIZED_FIELD, $field);
        $value = is_array($value) ? $value : [$value];

        $this->applyFilter($value, $operator, $field);

        return $this;
    }

    /**
     * Check if value is valid
     *
     * @param AttributeInterface $attribute
     * @param string             $operator
     * @param mixed              $value
     */
    protected function checkValue(AttributeInterface $attribute, $operator, $value)
    {
        if (!is_array($value) && Operators::IS_EMPTY !== $operator) {
            throw InvalidArgumentException::arrayExpected($attribute->getCode(), 'filter', 'options');
        }

        if (Operators::IS_EMPTY !== $operator) {
            foreach ($value as $option) {
                if (!is_numeric($option)) {
                    throw InvalidArgumentException::numericExpected($attribute->getCode(), 'filter', 'options');
                }
            }
        }
    }

    /**
     * Apply the filter to the query with the given operator
     *
     * @param array  $value
     * @param string $operator
     * @param string $field
     */
    protected function applyFilter(array $value, $operator, $field)
    {
        if ($operator === Operators::NOT_IN_LIST) {
            $this->qb->field($field)->notIn($value);
        } else {
            if (Operators::IS_EMPTY === $operator) {
                $expr = $this->qb->expr()->field($field)->exists(false);
                $this->qb->addAnd($expr);
            } else {
                $value = array_map('intval', $value);
                $expr = $this->qb->expr()->field($field.'.id')->in($value);
                $this->qb->addAnd($expr);
            }
        }
    }
}
