<?php

namespace Oro\Bundle\ImapBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;
use Oro\Bundle\EmailBundle\Entity\EmailFolder;

class ImapEmailRepository extends EntityRepository
{
//    public function getLastEmail(EmailFolder $folder)
//    {
//        $qb = $this->createQueryBuilder('e')
//            ->select('MAX(e.id)')
//            ->innerJoin('e.fromEmailAddress', 'fa')
//            ->innerJoin('e.folder', 'f')
//            ->innerJoin('r.origin', 'o')
//            ->where('fa.email = :email AND o.name = :origin');
//
//        $qb
//            ->setParameter('email', $fromEmail)
//            ->setParameter('origin', $originName);
//
//        $maxId = $qb->getQuery()->getSingleResult();
//
//        return $maxId === null
//            ? -1
//            : (int) $maxId;
//    }

//    public function getLastSyncInfo()
//    {
//        $conn = $this->getEntityManager()->getConnection();
//        $conn->executeQuery()
//    }
//
//    protected function getLastSyncInfoSql()
//    {
//        $emailEntityMetadata = $this->getEntityManager()->getRepository('OroEmailBundle:Email')->getClassMetadata();
//        $emailTableName = $emailEntityMetadata->getTableName();
//        $emailInternalDateColumnName = $emailEntityMetadata->getColumnName('internaldate');
//        $emailEntityMetadata = $this->getEntityManager()->getRepository('OroEmailBundle:Email')->getClassMetadata();
//
//        $sql = <<<SQL
//          SELECT s.uid, s.uid_validity, e.{$emailInternalDateColumnName}
//          FROM {$this->getClassMetadata()->getTableName()} as s
//          INNER JOIN {$emailTableName} as e ON e.id = s.email_id
//          INNER JOIN {$emailTableName} as e ON e.id = s.email_id
//          WHERE
//SQL;
//    }
}
