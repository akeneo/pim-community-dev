<?php

namespace Oro\Bundle\NotificationBundle\Provider;

use Doctrine\ORM\EntityManager;

class EmailNotificationGridHelper
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
     * @return array
     */
    public function getRecipientUsersChoices()
    {
        return $this->getEntityChoices(
            'OroUserBundle:User',
            'e.id, e.firstName, e.lastName'
        );
    }

    /**
     * @return array
     */
    public function getRecipientGroupsChoices()
    {
        return $this->getEntityChoices('OroUserBundle:Group', 'e.id, e.name', 'name');
    }

    /**
     * @param string $entity
     * @param string $select
     * @param string $mainField
     *
     * @return array
     */
    protected function getEntityChoices($entity, $select, $mainField = null)
    {
        $options = [];
        $entities = $this->em
            ->createQueryBuilder()
            ->from($entity, 'e')
            ->select($select)
            ->getQuery()
            ->getArrayResult();

        foreach ($entities as $entityItem) {
            if (is_null($mainField)) {
                $id = $entityItem['id'];
                unset($entityItem['id']);

                $options[$id] = implode(' ', $entityItem);
            } else {
                $options[$entityItem['id']] = $entityItem[$mainField];
            }
        }

        return $options;
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
