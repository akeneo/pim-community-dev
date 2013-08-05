<?php

namespace Oro\Bundle\EmailBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;
use Oro\Bundle\EmailBundle\Entity\Util\EmailUtil;

class EmailRepository extends EntityRepository
{
    /**
     * Return a query builder for get a list of emails which were sent to or from given email addresses
     * The emails are ordered by Sent date in reverse order
     *
     * @param string|string[]|\Traversable $emails The list of email addresses
     * @param null|integer $firstResult The index of the first result to retrieve
     * @param null|integer $maxResults The maximum number of results to retrieve
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getEmailListQueryBuilder($emails, $firstResult = null, $maxResults = null)
    {
        if (is_string($emails)) {
            $emails = array($emails);
        }
        $qbRecipients =
            $this->getEntityManager()->createQueryBuilder()
                ->select('re.id')
                ->from('OroEmailBundle:Email', 're')
                ->innerJoin('re.recipients', 'r')
                ->innerJoin('r.emailAddress', 'ra');
        $qbRecipients->where($qbRecipients->expr()->in('ra.email', $emails));

        $qb = $this->createQueryBuilder('e')
            ->select('partial e.{id, fromName, subject, sentAt}, a')
            ->innerJoin('e.fromEmailAddress', 'a')
            ->orderBy('e.sentAt', 'DESC');
        $qb->where(
            $qb->expr()->orX(
                $qb->expr()->in('e.id', $qbRecipients->getDQL()),
                $qb->expr()->in('a.email', $emails)
            )
        );

        if ($firstResult !== null) {
            $qb->setFirstResult($firstResult);
        }
        if ($maxResults !== null) {
            $qb->setMaxResults($maxResults);
        }

        return $qb;
    }
}
