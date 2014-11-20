<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter;

use Pim\Bundle\CatalogBundle\Doctrine\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Doctrine\Query\Operators;
use Pim\Bundle\CatalogBundle\Doctrine\Query\FieldFilterInterface;

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
        $this->checkValue($field, $value);

        $rootAlias  = $this->qb->getRootAlias();
        $entityAlias = 'filter'.$field;
        $this->qb->leftJoin($rootAlias.'.'.$field, $entityAlias);

        if ($operator === Operators::NOT_IN_LIST) {
            $this->qb->andWhere(
                $this->qb->expr()->orX(
                    $this->qb->expr()->notIn($entityAlias.'.id', $value),
                    $this->qb->expr()->isNull($entityAlias.'.id')
                )
            );
        } else {
            // TODO: fix this weird support of EMPTY operator
            if (in_array('empty', $value)) {
                unset($value[array_search('empty', $value)]);
                $exprNull = $this->qb->expr()->isNull($entityAlias.'.id');

                if (count($value) > 0) {
                    $exprIn = $this->qb->expr()->in($entityAlias.'.id', $value);
                    $expr = $this->qb->expr()->orX($exprNull, $exprIn);
                } else {
                    $expr = $exprNull;
                }
            } else {
                $expr = $this->qb->expr()->in($entityAlias.'.id', $value);
            }

            $this->qb->andWhere($expr);
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
        if (!is_array($value)) {
            throw InvalidArgumentException::arrayExpected($field, 'filter', 'entity');
        }

        foreach ($value as $entity) {
            if (!is_numeric($entity) && 'empty' !== $entity) {
                throw InvalidArgumentException::integerExpected($field, 'filter', 'entity');
            }
        }
    }
}
