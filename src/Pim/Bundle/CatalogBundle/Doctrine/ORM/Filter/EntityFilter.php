<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter;

use Pim\Bundle\CatalogBundle\Doctrine\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Doctrine\Query\Operators;
use Pim\Bundle\CatalogBundle\Doctrine\Query\FieldFilterInterface;
use Pim\Bundle\CatalogBundle\Doctrine\Query\FieldFilterHelper;

/**
 * Entity filter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EntityFilter extends AbstractFilter implements FieldFilterInterface
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
    public function addFieldFilter($field, $operator, $value, $locale = null, $scope = null)
    {
        if (Operators::IS_EMPTY !== $operator) {
            $this->checkValue($field, $value);
        }

        $rootAlias  = $this->qb->getRootAlias();
        $entityAlias = 'filter' . FieldFilterHelper::getCode($field);
        $this->qb->leftJoin($rootAlias . '.' . FieldFilterHelper::getCode($field), $entityAlias);

        if ($operator === Operators::IN_LIST) {
            $this->qb->andWhere(
                $this->qb->expr()->in($entityAlias . '.' . FieldFilterHelper::getProperty($field), $value)
            );
        } elseif ($operator === Operators::NOT_IN_LIST) {
            $this->qb->andWhere(
                $this->qb->expr()->orX(
                    $this->qb->expr()->notIn($entityAlias . '.' . FieldFilterHelper::getProperty($field), $value),
                    $this->qb->expr()->isNull($entityAlias.'.id')
                )
            );
        } elseif ($operator === Operators::IS_EMPTY) {
            $this->qb->andWhere(
                $this->qb->expr()->isNull($entityAlias.'.id')
            );
        }

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
     * Check if value is valid
     *
     * @param string $field
     * @param mixed  $value
     */
    protected function checkValue($field, $value)
    {
        //TODO : cyclomatic complexity is too high
        if (!is_array($value)) {
            throw InvalidArgumentException::arrayExpected($field, 'filter', 'entity');
        }

        foreach ($value as $entity) {
            if ((
                    FieldFilterHelper::hasProperty($field) &&
                    FieldFilterHelper::getProperty($field) === 'id' &&
                    !is_numeric($entity)
                ) ||
                (
                    !FieldFilterHelper::hasProperty($field) &&
                    !is_numeric($entity)
                )
            ) {
                throw InvalidArgumentException::integerExpected($field, 'filter', 'entity');
            } elseif (FieldFilterHelper::hasProperty($field) &&
                FieldFilterHelper::getProperty($field) !== 'id'
                && !is_string($entity)
            ) {
                throw InvalidArgumentException::stringExpected($field, 'filter', 'entity');
            }
        }
    }
}
