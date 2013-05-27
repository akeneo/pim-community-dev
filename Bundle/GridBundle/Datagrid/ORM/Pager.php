<?php

namespace Oro\Bundle\GridBundle\Datagrid\ORM;

use Sonata\DoctrineORMAdminBundle\Datagrid\Pager as BasePager;

use Oro\Bundle\GridBundle\Datagrid\PagerInterface;
use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;

class Pager extends BasePager implements PagerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getNbResults()
    {
        return intval(parent::getNbResults());
    }

    /**
     * {@inheritdoc}
     */
    public function computeNbResult()
    {
        return $this->getQuery()->getTotalCount();
    }

    /**
     * @return ProxyQueryInterface
     */
    public function getQuery()
    {
        return parent::getQuery();
    }
}
