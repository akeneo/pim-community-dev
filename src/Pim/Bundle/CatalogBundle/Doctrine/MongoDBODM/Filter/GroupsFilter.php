<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter;

use Pim\Bundle\CatalogBundle\Doctrine\Common\Filter\ObjectIdResolverInterface;
use Pim\Bundle\CatalogBundle\Query\Filter\FieldFilterHelper;
use Pim\Bundle\CatalogBundle\Query\Filter\FieldFilterInterface;
use Pim\Bundle\CatalogBundle\Query\Filter\Operators;

/**
 * Groups filter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupsFilter extends AbstractFilter implements FieldFilterInterface
{
    /** @var array */
    protected $supportedFields;

    /** @var ObjectIdResolverInterface */
    protected $objectIdResolver;

    /**
     * Instanciate the filter
     *
     * @param ObjectIdResolverInterface $objectIdResolver
     * @param array                     $supportedFields
     * @param array                     $supportedOperators
     */
    public function __construct(
        ObjectIdResolverInterface $objectIdResolver,
        array $supportedFields = [],
        array $supportedOperators = []
    ) {
        $this->objectIdResolver   = $objectIdResolver;
        $this->supportedFields    = $supportedFields;
        $this->supportedOperators = $supportedOperators;
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
    public function addFieldFilter($field, $operator, $value, $locale = null, $scope = null, $options = [])
    {
        $this->checkValue($field, $value);

        $value = is_array($value) ? $value : [$value];
        if (FieldFilterHelper::getProperty($field) === FieldFilterHelper::CODE_PROPERTY) {
            $value = $this->objectIdResolver->getIdsFromCodes('group', $value);
        }
        $value = array_map('intval', $value);

        $this->applyFilter($value, 'groupIds', $operator);

        return $this;
    }

    /**
     * Check if value is valid
     *
     * @param string $field
     * @param mixed  $values
     */
    protected function checkValue($field, $values)
    {
        FieldFilterHelper::checkArray($field, $values, 'groups');

        foreach ($values as $value) {
            FieldFilterHelper::checkIdentifier($field, $value, 'groups');
        }
    }

    /**
     * Apply the filter to the query with the given operator
     *
     * @param array  $value
     * @param string $field
     * @param string $operator
     */
    protected function applyFilter(array $value, $field, $operator)
    {
        if ($operator === Operators::NOT_IN_LIST) {
            $this->qb->field($field)->notIn($value);
        } else {
            $this->qb->field($field)->in($value);
        }
    }
}
