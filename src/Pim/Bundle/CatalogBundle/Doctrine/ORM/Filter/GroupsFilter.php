<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter;

use Pim\Bundle\CatalogBundle\Doctrine\Common\Filter\ObjectIdResolverInterface;
use Pim\Bundle\CatalogBundle\Query\Filter\FieldFilterHelper;
use Pim\Bundle\CatalogBundle\Query\Filter\FieldFilterInterface;
use Pim\Bundle\CatalogBundle\Query\Filter\Operators;

/**
 * Entity filter
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
     * Instanciate the base filter
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
    public function addFieldFilter($field, $operator, $value, $locale = null, $scope = null, $options = [])
    {
        if (Operators::IS_EMPTY !== $operator) {
            $this->checkValue($field, $value);

            if (FieldFilterHelper::getProperty($field) === FieldFilterHelper::CODE_PROPERTY) {
                $value = $this->objectIdResolver->getIdsFromCodes('group', $value);
            }
        }

        $rootAlias   = $this->qb->getRootAlias();
        $entityAlias = $this->getUniqueAlias('filter' . FieldFilterHelper::getCode($field));
        $this->qb->leftJoin($rootAlias . '.' . FieldFilterHelper::getCode($field), $entityAlias);

        if ($operator === Operators::IN_LIST) {
            $this->qb->andWhere(
                $this->qb->expr()->in($entityAlias . '.id', $value)
            );
        } elseif ($operator === Operators::NOT_IN_LIST) {
            $this->qb->andWhere(
                $this->qb->expr()->orX(
                    $this->qb->expr()->notIn($entityAlias . '.id', $value),
                    $this->qb->expr()->isNull($entityAlias . '.id')
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
