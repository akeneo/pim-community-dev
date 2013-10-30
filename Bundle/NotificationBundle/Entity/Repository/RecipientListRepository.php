<?php

namespace Oro\Bundle\NotificationBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

use Oro\Bundle\NotificationBundle\Entity\RecipientList;
use Oro\Bundle\TagBundle\Entity\ContainAuthorInterface;
use Oro\Bundle\EmailBundle\Model\EmailHolderInterface;

class RecipientListRepository extends EntityRepository
{
    /**
     * @param RecipientList $recipientList
     * @param $entity
     * @return array
     */
    public function getRecipientEmails(RecipientList $recipientList, $entity)
    {
        // get user emails
        $emails = $recipientList->getUsers()->map(
            function (EmailHolderInterface $user) {
                return $user->getEmail();
            }
        );

        $groupIds = $recipientList->getGroups()->map(
            function ($group) {
                return $group->getId();
            }
        )->toArray();

        if ($groupIds) {
            $groupUsers = $this->_em->createQueryBuilder()
                ->select('u.email')
                ->from('OroUserBundle:User', 'u')
                ->leftJoin('u.groups', 'groups')
                ->where('groups.id IN (:groupIds)')
                ->setParameter('groupIds', $groupIds)
                ->getQuery()
                ->getResult();

            // add group users emails
            array_map(
                function ($groupEmail) use ($emails) {
                    $emails[] = $groupEmail['email'];
                },
                $groupUsers
            );
        }

        // add owner email
        if ($recipientList->getOwner() && $entity instanceof ContainAuthorInterface) {
            $emails[] = $entity->getCreatedBy()->getEmail();
        }

        // add custom email
        if ($recipientList->getEmail()) {
            $emails[] = $recipientList->getEmail();
        }

        return array_unique($emails->toArray());
    }
}
