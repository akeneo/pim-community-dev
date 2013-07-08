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

        $qb
            ->add('where', $qb->expr()->eq($alias . '.tag', $this->tag->getId()))
            ->addGroupBy($alias . '.entityName')
            ->addGroupBy($alias . '.recordId');

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
