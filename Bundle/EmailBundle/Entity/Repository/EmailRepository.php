<?php

namespace Oro\Bundle\EmailBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use \Doctrine\ORM\QueryBuilder;
use \Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr;

class EmailRepository extends EntityRepository
{
    const EMAIL_ADDRESSES = 'email_addresses';

    /**
     * Return a query builder which may be used to get a list of emails
     * The emails are ordered by Sent date in reverse order
     *
     * @param null|integer $firstResult The index of the first result to retrieve
     * @param null|integer $maxResults The maximum number of results to retrieve
     * @return QueryBuilder
     */
    public function createEmailListQueryBuilder($firstResult = null, $maxResults = null)
    {
        $qb = $this->createQueryBuilder('e')
            ->select('partial e.{id, fromName, subject, sentAt}, a')
            ->innerJoin('e.fromEmailAddress', 'a')
            ->orderBy('e.sentAt', 'DESC');

        if ($firstResult !== null) {
            $qb->setFirstResult($firstResult);
        }
        if ($maxResults !== null) {
            $qb->setMaxResults($maxResults);
        }

        return $qb;
    }

    /**
     * Return a query builder which may be used to get a list of emails related to a list of email addresses
     * The emails are ordered by Sent date in reverse order
     *
     * @param null|integer $firstResult The index of the first result to retrieve
     * @param null|integer $maxResults The maximum number of results to retrieve
     * @return QueryBuilder
     */
    public function createEmailListForAddressesQueryBuilder($firstResult = null, $maxResults = null)
    {
        $qbRecipients =
            $this->getEntityManager()->createQueryBuilder()
                ->select('re.id')
                ->from('OroEmailBundle:Email', 're')
                ->innerJoin('re.recipients', 'r')
                ->innerJoin('r.emailAddress', 'ra');
        $qbRecipients->where(sprintf('ra.email IN (:%s)', self::EMAIL_ADDRESSES));

        $qb = $this->createEmailListQueryBuilder($firstResult, $maxResults);
        $qb->where(sprintf('a.email IN (:%s)', self::EMAIL_ADDRESSES));
        $qb->orWhere($qb->expr()->in('e.id', $qbRecipients->getDQL()));

        return $qb;
    }
}
