<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter;

use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Query\Filter\AttributeFilterInterface;
use Pim\Component\Catalog\Query\Filter\FieldFilterHelper;
use Pim\Component\Catalog\Query\Filter\FieldFilterInterface;
use Pim\Component\Catalog\Validator\AttributeValidatorHelper;

/**
 * Identifier filter.
 *
 * Temporary class used during the ES transition. Will be dropped as soon as ES search will
 * be implemented. It's purpose is only to make the Behat and integration tests pass.
 *
 * Query products on the "identifier" field.
 * If a query if performed on the identifier attribute (like "sku" for instance), the search
 * will be routed to the "identifier" field.
 *
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IdentifierFilter extends StringFilter implements AttributeFilterInterface, FieldFilterInterface
{
    /** @var array */
    protected $supportedFields;

    /**
     * @param AttributeValidatorHelper $attrValidatorHelper
     * @param array                    $supportedAttributeTypes
     * @param array                    $supportedFields
     * @param array                    $supportedOperators
     */
    public function __construct(
        AttributeValidatorHelper $attrValidatorHelper,
        array $supportedAttributeTypes = [],
        array $supportedFields = [],
        array $supportedOperators = []
    ) {
        $this->supportedFields = $supportedFields;
        parent::__construct($attrValidatorHelper, $supportedAttributeTypes, $supportedOperators);
    }

    /**
     * {@inheritdoc}
     */
    public function addAttributeFilter(
        AttributeInterface $attribute,
        $operator,
        $value,
        $locale = null,
        $scope = null,
        $options = []
    ) {
        $this->addFieldFilter(current($this->supportedFields), $operator, $value, $locale, $scope, $options);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldFilter($field, $operator, $value, $locale = null, $scope = null, $options = [])
    {
        $this->checkScalarValue($field, $value);

        $field = current($this->qb->getRootAliases()) . '.' . FieldFilterHelper::getCode($field);
        $condition = $this->prepareCondition($field, $operator, $value);
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

    /**
     * {@inheritdoc}
     */
    public function getFields()
    {
        return $this->supportedFields;
    }
}
