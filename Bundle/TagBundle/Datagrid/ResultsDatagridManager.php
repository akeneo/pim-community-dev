<?php

namespace Oro\Bundle\TagBundle\Datagrid;

use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\TagBundle\Entity\Tag;
use Oro\Bundle\GridBundle\Datagrid\DatagridManager;
use Oro\Bundle\SearchBundle\Datagrid\SearchDatagridManager;

class ResultsDatagridManager extends SearchDatagridManager
{
    /**
     * @var Tag
     */
    protected $tag;

    /**
     * {@inheritDoc}
     */
    protected function createQuery()
    {
        /** @var ResultsQuery $query */
        $query = DatagridManager::createQuery();

        /** @var QueryBuilder $qb */
        $qb = $query->getQueryBuilder();
        $aliases = $qb->getRootAliases();
        $alias = end($aliases);

        $qb->where($alias . '.tag = :tag')
            ->setParameter('tag', $this->tag->getId());
        $qb->addGroupBy($alias . '.entityName')
            ->addGroupBy($alias . '.recordId');

        if ($this->searchEntity != '*') {
            $qb->andWhere($alias . '.alias = :alias')
                ->setParameter('alias', $this->searchEntity);
        }

        return $query;
    }

    /**
     * Setter for tag object
     *
     * @param Tag $tag
     * @return $this
     */
    public function setTag(Tag $tag)
    {
        $this->tag = $tag;

        return $this;
    }
}
