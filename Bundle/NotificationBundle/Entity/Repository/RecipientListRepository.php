<?php

namespace Oro\Bundle\NotificationBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Oro\Bundle\NotificationBundle\Entity\RecipientList;

class RecipientListRepository extends EntityRepository
{
    /**
     * @param RecipientList $recipientList
     * @return array
     */
    public function getRecipientEmails(RecipientList $recipientList)
    {
        $emails = $recipientList->getUsers()->map(
            function ($user) {
                return $user->getEmail();
            }
        );

        $groupIds = $recipientList->getGroups()->map(
            function ($group) {
                return $group->getId();
            }
        )->toArray();

        $groupUsers = $this->_em->createQueryBuilder()
            ->select('u.email')
            ->from('OroUserBundle:User', 'u')
            ->leftJoin('u.groups', 'groups')
            ->where('groups.id IN (:groupIds)')
            ->setParameter('groupIds', $groupIds)
            ->getQuery()
            ->getResult();

        array_map(
            function ($groupEmail) use ($emails) {
                $emails[] = $groupEmail['email'];
            },
            $groupUsers
        );

        return array_unique($emails->toArray());
    }
}
