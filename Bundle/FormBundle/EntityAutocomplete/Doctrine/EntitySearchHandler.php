<?php

namespace Oro\Bundle\FormBundle\EntityAutocomplete\Doctrine;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\Expr;

use Oro\Bundle\FormBundle\EntityAutocomplete\SearchPropertyConfig;
use Oro\Bundle\FormBundle\EntityAutocomplete\SearchHandlerInterface;

class EntitySearchHandler implements SearchHandlerInterface
{
    /**
     * @var QueryBuilder
     */
    protected $queryBuilderSearchHandler;

    /**
     * @param EntityManager $em
     * @param string $className
     * @param SearchPropertyConfig[] $searchProperties
     */
    public function __construct(EntityManager $em, $className, array $searchProperties)
    {
        $queryBuilder = $em->getRepository($className)->createQueryBuilder('e');
        $this->queryBuilderSearchHandler = new QueryBuilderSearchHandler($queryBuilder, 'e', $searchProperties);
    }

    /**
     * {@inheritdoc}
     */
    public function search($search, $page, $perPage)
    {
        return $this->queryBuilderSearchHandler->search($search, $page, $perPage);
    }
}
