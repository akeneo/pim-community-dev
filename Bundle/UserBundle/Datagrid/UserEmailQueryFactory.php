<?php

namespace Oro\Bundle\UserBundle\Datagrid;

use Oro\Bundle\EmailBundle\Datagrid\EmailQueryFactory;
use Oro\Bundle\EmailBundle\Entity\EmailFolder;
use Oro\Bundle\EmailBundle\Entity\InternalEmailOrigin;
use Oro\Bundle\GridBundle\Datagrid\ORM\EntityProxyQuery;
use Oro\Bundle\EmailBundle\Entity\Repository\EmailRepository;

class UserEmailQueryFactory extends EmailQueryFactory
{
    /**
     * {@inheritDoc}
     */
    public function createQuery()
    {
        $entityManager = $this->registry->getManagerForClass($this->className);
        /** @var EmailRepository $repository */
        $repository         = $entityManager->getRepository($this->className);
        $this->queryBuilder = $repository->createEmailListQueryBuilder();
        $this->prepareQuery($this->queryBuilder);
        $this->queryBuilder
            ->addSelect('partial f.{id, name, type}')
            ->innerJoin('e.folder', 'f')
            ->innerJoin('f.origin', 'o')
            ->where('o.id = :origin_id');

        // @todo: need to reflect in new grid as well
        $bapOriginQueryBuilder = $this->queryBuilder
            ->getEntityManager()
            ->getRepository('OroEmailBundle:InternalEmailOrigin')
            ->createQueryBuilder('bapOrigin')
            ->innerJoin('bapOrigin.folders', 'bapOriginFolders')
            ->innerJoin('bapOriginFolders.emails', 'bapOriginEmails')
            ->innerJoin('bapOriginEmails.fromEmailAddress', 'bapOriginFromEmailAddress')
            ->where(
                'bapOrigin.name = :bap_origin_name'
                . ' AND bapOriginFolders.type = :sent_folder_type'
                . ' AND bapOriginFromEmailAddress.email IN (:user_emails)'
                . ' AND bapOriginEmails.id = e.id'
            );
        $this->queryBuilder
            ->orWhere($this->queryBuilder->expr()->exists($bapOriginQueryBuilder->getDQL()))
            ->setParameter('bap_origin_name', InternalEmailOrigin::BAP)
            ->setParameter('sent_folder_type', EmailFolder::SENT);

        return new EntityProxyQuery($this->queryBuilder);
    }
}
