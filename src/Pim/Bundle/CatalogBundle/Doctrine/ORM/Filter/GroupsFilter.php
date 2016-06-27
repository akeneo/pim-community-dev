<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter;

use Pim\Bundle\CatalogBundle\Doctrine\Common\Filter\ObjectIdResolverInterface;
use Pim\Component\Catalog\Query\Filter\FieldFilterHelper;
use Pim\Component\Catalog\Query\Filter\FieldFilterInterface;
use Pim\Component\Catalog\Query\Filter\Operators;

/**
 * Entity filter
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
        $this->objectIdResolver   = $objectIdResolver;
        $this->supportedFields    = $supportedFields;
        $this->supportedOperators = $supportedOperators;
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldFilter($field, $operator, $value, $locale = null, $scope = null, $options = [])
    {
        if (Operators::IS_EMPTY !== $operator && Operators::IS_NOT_EMPTY !== $operator) {
            $this->checkValue($field, $value);

            if (FieldFilterHelper::getProperty($field) === FieldFilterHelper::CODE_PROPERTY) {
                $value = $this->objectIdResolver->getIdsFromCodes('group', $value);
            }
        }

        $rootAlias   = $this->qb->getRootAlias();
        $entityAlias = $this->getUniqueAlias('filter' . FieldFilterHelper::getCode($field));
        $this->qb->leftJoin($rootAlias . '.' . FieldFilterHelper::getCode($field), $entityAlias);

        switch ($operator) {
            case Operators::IN_LIST:
                $this->qb->andWhere(
                    $this->qb->expr()->in($entityAlias . '.id', $value)
                );
                break;
            case Operators::NOT_IN_LIST:
                $this->qb->andWhere($this->qb->expr()->notIn(
                    $rootAlias . '.id',
                    $this->getNotInSubquery(FieldFilterHelper::getCode($field), $value)
                ));
                break;
            case Operators::IS_EMPTY:
            case Operators::IS_NOT_EMPTY:
                $this->qb->andWhere(
                    $this->prepareCriteriaCondition($entityAlias . '.id', $operator, null)
                );
                break;
        }

        return $this;
    }

    /**
     * Subquery matching all products that actually have one of $value groups
     *
     * @param string $field
     * @param array  $value
     *
     * @return string
     */
    protected function getNotInSubquery($field, $value)
    {
        $notInQb      = $this->qb->getEntityManager()->createQueryBuilder();
        $rootEntity   = current($this->qb->getRootEntities());
        $notInAlias   = $this->getUniqueAlias('productsNotIn');
        $joinAlias    = $this->getUniqueAlias('filter' . $field);

        $notInQb->select($notInAlias . '.id')
            ->from($rootEntity, $notInAlias, $notInAlias . '.id')
            ->innerJoin(
                sprintf('%s.%s', $notInQb->getRootAlias(), $field),
                $joinAlias
            )
            ->where($notInQb->expr()->in($joinAlias . '.id', $value));

        return $notInQb->getDQL();
    }

    /**
     * Check if value is valid
     *
     * @param string $field
     * @param mixed  $values
     */
    protected function checkValue($field, $values)
    {
        FieldFilterHelper::checkArray($field, $values, FieldFilterHelper::getCode($field));

        foreach ($values as $value) {
            FieldFilterHelper::checkIdentifier($field, $value, FieldFilterHelper::getCode($field));
        }
    }
}
