<?php

namespace Oro\Bundle\EmailBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;
use Oro\Bundle\EmailBundle\Entity\Util\EmailUtil;

class EmailRepository extends EntityRepository
{
    /**
     * Return a query builder for get a list of emails
     *
     * @param string|string[]|\Traversable $recipients The list of recipients' email addresses
     * @param null|integer $firstResult The index of the first result to retrieve
     * @param null|integer $maxResults The maximum number of results to retrieve
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getEmailListQueryBuilder($recipients, $firstResult = null, $maxResults = null)
    {
        $qbRecipients =
            $this->getEntityManager()->createQueryBuilder()
            ->select('re.id')
            ->from('OroEmailBundle:Email', 're')
            ->innerJoin('re.recipients', 'r')
            ->innerJoin('r.emailAddress', 'ra');
        $emails = array();
        if ($recipients instanceof \Traversable) {
            foreach ($recipients as $recipient) {
                $emails[] = EmailUtil::extractPureEmailAddress($recipient);
            }
        } else {
            $emails[] = EmailUtil::extractPureEmailAddress($recipients);
        }
        $qbRecipients->where($qbRecipients->expr()->in('ra.email', $emails));

        $qb = $this->createQueryBuilder('e')
            ->select('partial e.{id, fromName, subject, sentAt}, a')
            ->innerJoin('e.fromEmailAddress', 'a')
            ->orderBy('e.created', 'DESC');
        $qb->where($qb->expr()->in('e.id', $qbRecipients->getDQL()));

        if ($firstResult !== null) {
            $qb->setFirstResult($firstResult);
        }
        if ($maxResults !== null) {
            $qb->setMaxResults($maxResults);
        }

        return $qb;
    }
}
