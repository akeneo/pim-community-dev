<?php

namespace Oro\Bundle\UserBundle\Datagrid;

use Oro\Bundle\GridBundle\Datagrid\ORM\QueryFactory\EntityQueryFactory;
use Oro\Bundle\GridBundle\Datagrid\ORM\EntityProxyQuery;
use Oro\Bundle\EmailBundle\Entity\Repository\EmailRepository;

class UserEmailQueryFactory extends EntityQueryFactory
{
    /**
     * {@inheritDoc}
     */
    public function createQuery()
    {
        $entityManager = $this->registry->getManagerForClass($this->className);
        /** @var EmailRepository $repository */
        $repository = $entityManager->getRepository($this->className);
        $this->queryBuilder = $repository->createEmailListQueryBuilder();
        $this->queryBuilder
            ->addSelect('partial f.{id, name, type}')
            ->innerJoin('e.folder', 'f')
            ->innerJoin('f.origin', 'o')
            ->where('o.id = :origin_id');

        return new EntityProxyQuery($this->queryBuilder);
    }
}
