<?php

namespace Oro\Bundle\NotificationBundle\EventListener;

use Doctrine\ORM\EntityManager;

use Oro\Bundle\DataGridBundle\Event\BuildAfter;

class EmailNotificationGridListener
{
    /** @var EntityManager */
    protected $em;

    /** @var array */
    protected $entityNameChoices;

    /**
     * @param EntityManager $em
     * @param array $entitiesConfig
     */
    public function __construct(EntityManager $em, $entitiesConfig = array())
    {
        $this->em = $em;

        $this->entityNameChoices = array_map(
            function ($value) {
                return isset($value['name'])? $value['name'] : '';
            },
            $entitiesConfig
        );
    }

    /**
     * @param BuildAfter $event
     */
    public function buildAfter(BuildAfter $event)
    {
        //
    }

    /**
     * @return array
     */
    public function getRecipientUsersChoices()
    {
        $fullNames = $this->em
            ->createQueryBuilder('e')
            ->select('e.id, e.fullName')
            ->getQuery()
            ->getArrayResult();

        $options = [];

        foreach ($fullNames as $fullNameItem) {
            $options[$fullNameItem['id']] = $fullNameItem['fullName'];
        }

        return $options;
    }

    public function getRecipientGroupsChoices()
    {
        
    }

    /**
     * @return array
     */
    public function getEventNameChoices()
    {
        return $this->em
            ->getRepository('OroNotificationBundle:Event')
            ->getEventNamesChoices();
    }

    /**
     * @return array
     */
    public function getEntityNameChoices()
    {
        return $this->entityNameChoices;
    }
}
