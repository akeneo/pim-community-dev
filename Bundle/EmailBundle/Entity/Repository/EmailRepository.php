<?php

namespace Oro\Bundle\EmailBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;
use Oro\Bundle\EmailBundle\Entity\EmailInterface;
use Oro\Bundle\EmailBundle\Entity\Util\EmailUtil;
use Oro\Bundle\UserBundle\Entity\User;

class EmailRepository extends EntityRepository
{
    /** @var  User|Contact, etc */
    protected $entity;

    /**
     * Return a query builder for get a list of emails which were sent to or from given email addresses
     * The emails are ordered by Sent date in reverse order
     *
     * @param string[] $emails The list of email addresses
     * @param null|integer $firstResult The index of the first result to retrieve
     * @param null|integer $maxResults The maximum number of results to retrieve
     * @return \Doctrine\ORM\QueryBuilder
     * @throws \InvalidArgumentException
     */
    public function getEmailListQueryBuilder(array $emails, $firstResult = null, $maxResults = null)
    {
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

    public function createQueryBuilder($alias)
    {
        $emails = $this->extractEmailAddresses($this->entity->getEmails());

        // TODO: remove this afer User will have getPrimaryEmail method, like Contact
        if (method_exists($this->entity, 'getEmail')) {
            $emails[] =  $this->entity->getEmail();
        }

        if (empty($emails)) {
            $emails = array(null);
        }

        $qbRecipients =
            $this->getEntityManager()->createQueryBuilder()
                ->select('re.id')
                ->from('OroEmailBundle:Email', 're')
                ->innerJoin('re.recipients', 'r')
                ->innerJoin('r.emailAddress', 'ra');
        $qbRecipients->where($qbRecipients->expr()->in('ra.email', $emails));

        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('e')
            ->from($this->getClassName(), 'e')
            ->select('partial e.{id, fromName, subject, sentAt}, a')
            ->innerJoin('e.fromEmailAddress', 'a');
        $qb->where(
            $qb->expr()->orX(
                $qb->expr()->in('e.id', $qbRecipients->getDQL()),
                $qb->expr()->in('a.email', $emails)
            )
        );

        return $qb;
    }

    /**
     * @param $entity
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;
    }

    /**
     * Extract email addresses from the given argument.
     * Always return an array, even if no any email is given.
     *
     * @param $emails
     * @return string[]
     * @throws \InvalidArgumentException
     */
    protected function extractEmailAddresses($emails)
    {
        if (is_string($emails)) {
            return empty($emails)
                ? array()
                : array($emails);
        }
        if (!is_array($emails) && !($emails instanceof \Traversable)) {
            throw new \InvalidArgumentException('The emails argument must be a string, array or collection.');
        }

        $result = array();
        foreach ($emails as $email) {
            if (is_string($email)) {
                $result[] = $email;
            } elseif ($email instanceof EmailInterface) {
                $result[] = $email->getEmail();
            } else {
                throw new \InvalidArgumentException(
                    'Each item of the emails collection must be a string or an object of EmailInterface.'
                );
            }
        }

        return $result;
    }
}
