<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter;

use Pim\Bundle\CatalogBundle\Doctrine\Common\Filter\ObjectIdResolverInterface;
use Pim\Component\Catalog\Query\Filter\FieldFilterHelper;
use Pim\Component\Catalog\Query\Filter\FieldFilterInterface;
use Pim\Component\Catalog\Query\Filter\Operators;

/**
 * Groups filter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupsFilter extends AbstractFieldFilter implements FieldFilterInterface
{
    /** @var ObjectIdResolverInterface */
    protected $objectIdResolver;

    /**
     * @param ObjectIdResolverInterface $objectIdResolver
     * @param array                     $supportedFields
     * @param array                     $supportedOperators
     */
    public function __construct(
        ObjectIdResolverInterface $objectIdResolver,
        array $supportedFields = [],
        array $supportedOperators = []
    ) {
        $this->objectIdResolver = $objectIdResolver;
        $this->supportedFields = $supportedFields;
        $this->supportedOperators = $supportedOperators;
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldFilter($field, $operator, $value, $locale = null, $scope = null, $options = [])
    {
        if (Operators::IS_EMPTY !== $operator && Operators::IS_NOT_EMPTY !== $operator) {
            $this->checkValue($field, $value);

            if (FieldFilterHelper::CODE_PROPERTY === FieldFilterHelper::getProperty($field)) {
                $value = $this->objectIdResolver->getIdsFromCodes('group', $value);
            } else {
                $value = array_map('intval', $value);
            }
        }

        $this->applyFilter('groupIds', $operator, $value);

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
        FieldFilterHelper::checkArray($field, $values, static::class);

        foreach ($values as $value) {
            FieldFilterHelper::checkIdentifier($field, $value, static::class);
        }
    }

    /**
     * Apply the filter to the query with the given operator
     *
     * @param string     $field
     * @param string     $operator
     * @param array|null $value
     */
    protected function applyFilter($field, $operator, $value)
    {
        switch ($operator) {
            case Operators::IN_LIST:
                $this->qb->field($field)->in($value);
                break;
            case Operators::NOT_IN_LIST:
                $this->qb->field($field)->notIn($value);
                break;
            case Operators::IS_EMPTY:
                $this->qb->field($field)->size(0);
                break;
            case Operators::IS_NOT_EMPTY:
                $this->qb->where(sprintf('this.%s.length > 0', $field));
                break;
        }
    }
}
