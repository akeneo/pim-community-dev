<?php

namespace Oro\Bundle\FormBundle\EntityAutocomplete\Flexible;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;
use Oro\Bundle\FlexibleEntityBundle\Entity\Repository\FlexibleEntityRepository;

use Oro\Bundle\FormBundle\EntityAutocomplete\Doctrine\ExpressionFactory;
use Oro\Bundle\FormBundle\EntityAutocomplete\Property;
use Oro\Bundle\FormBundle\EntityAutocomplete\SearchHandlerInterface;

class FlexibleSearchHandler implements SearchHandlerInterface
{
    /**
     * @var FlexibleEntityRepository
     */
    protected $repository;

    /**
     * @var Property[]
     */
    protected $properties;

    /**
     * @var ExpressionFactory
     */
    protected $exprFactory;

    /**
     * @param FlexibleEntityRepository $repository
     * @param Property[] $properties
     */
    public function __construct(FlexibleEntityRepository $repository, array $properties)
    {
        $this->repository = $repository;
        $this->properties = $properties;
        $this->exprFactory = new ExpressionFactory();
    }

    /**
     * {@inheritdoc}
     */
    public function search($search, $firstResult, $maxResults)
    {
        $rootAlias = 'e';
        $queryBuilder = $this->repository->createFlexibleQueryBuilder($rootAlias);

        $attributes = $this->repository->getCodeToAttributes(array());
        $searchFields = array();

        foreach ($this->properties as $property) {
            $propertyName = $property->getName();
            if (empty($attributes[$propertyName])) {
                // simple property
                $searchFields[] = $property->getOption('entity_alias', $rootAlias) . '.' . $propertyName;
                continue;
            }
            /** @var $attribute AbstractAttribute */
            $attribute = $attributes[$propertyName];
            $joinAlias = 'join_' . $propertyName;
            $searchFields[] = $joinAlias . '.' . $attribute->getBackendType();
            $joinCondition = $queryBuilder->prepareAttributeJoinCondition($attribute, $joinAlias);
            $joinExpr = $rootAlias . '.' . $attribute->getBackendStorage();
            $queryBuilder->leftJoin($joinExpr, $joinAlias, 'WITH', $joinCondition);
        }

        foreach ($searchFields as $field) {
            $queryBuilder->addOrderBy($field);
        }

        if ($search && $searchFields) {
            if (count($searchFields) == 1) {
                $searchExpr = $searchFields[0];
            } else {
                $searchExpr = $this->exprFactory->multipleConcat($searchFields, ' ');
            }

            $queryBuilder->andWhere($this->exprFactory->like($searchExpr, ':search'));
            $queryBuilder->setParameter('search', '%' . $search. '%');
        }

        $queryBuilder->setFirstResult($firstResult)->setMaxResults($maxResults);
        $paginator = new Paginator($queryBuilder->getQuery(), true);

        return $paginator->getIterator()->getArrayCopy();
    }
}
