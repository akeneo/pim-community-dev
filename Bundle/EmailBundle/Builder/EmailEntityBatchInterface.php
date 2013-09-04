<?php

namespace Oro\Bundle\EmailBundle\Builder;

use Doctrine\ORM\EntityManager;

interface EmailEntityBatchInterface
{
    /**
     * Tell the given EntityManager to manage this batch
     *
     * @param EntityManager $em
     */
    public function persist(EntityManager $em);
}
