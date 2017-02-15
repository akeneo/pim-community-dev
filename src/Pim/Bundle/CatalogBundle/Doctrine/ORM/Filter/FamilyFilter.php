<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter;

use Pim\Component\Catalog\Query\Filter\FieldFilterHelper;
use Pim\Component\Catalog\Query\Filter\FieldFilterInterface;
use Pim\Component\Catalog\Query\Filter\Operators;

/**
 * Family filter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyFilter extends AbstractFieldFilter implements FieldFilterInterface
{
    /**
     * @param string[] $supportedFields
     * @param string[] $supportedOperators
     */
    public function __construct(array $supportedFields = [], array $supportedOperators = [])
    {
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
        }

        $rootAlias = $this->qb->getRootAlias();
        $entityAlias = $this->getUniqueAlias('filter' . FieldFilterHelper::getCode($field));
        $this->qb->leftJoin($rootAlias . '.' . FieldFilterHelper::getCode($field), $entityAlias);

        switch ($operator) {
            case Operators::IN_LIST:
                $this->qb->andWhere(
                    $this->qb->expr()->in($entityAlias . '.code', $value)
                );
                break;
            case Operators::NOT_IN_LIST:
                $this->qb->andWhere(
                    $this->qb->expr()->orX(
                        $this->qb->expr()->notIn($entityAlias . '.code', $value),
                        $this->qb->expr()->isNull($entityAlias . '.code')
                    )
                );
                break;
            case Operators::IS_EMPTY:
                $this->qb->andWhere(
                    $this->qb->expr()->isNull($entityAlias . '.code')
                );
                break;
            case Operators::IS_NOT_EMPTY:
                $this->qb->andWhere($this->qb->expr()->isNotNull($entityAlias . '.code'));
                break;
        }

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
}
