<?php

namespace Oro\Bundle\NotificationBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

use Oro\Bundle\NotificationBundle\Entity\Event;

class LoadDefaultNotificationEvents extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $eventNames = array(
            'update' => 'oro.notification.event.entity_post_update',
            'remove' => 'oro.notification.event.entity_post_remove',
            'create' => 'oro.notification.event.entity_post_persist'
        );

        foreach ($eventNames as $key => $name) {
            $event = new Event($name, 'Event dispatched whenever any entity ' . $key . 's');
            $manager->persist($event);
        }
        $manager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 120;
    }
}
