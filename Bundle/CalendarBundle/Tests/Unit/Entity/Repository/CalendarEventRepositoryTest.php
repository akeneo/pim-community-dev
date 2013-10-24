<?php

namespace Oro\Bundle\CalendarBundle\Tests\Unit\Entity\Repository;

use Doctrine\Tests\OrmTestCase;
use Doctrine\Tests\Mocks\EntityManagerMock;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Oro\Bundle\CalendarBundle\Entity\Repository\CalendarEventRepository;

class CalendarEventRepositoryTest extends OrmTestCase
{
    /**
     * @var EntityManagerMock
     */
    protected $em;

    protected function setUp()
    {
        $reader = new AnnotationReader();
        $metadataDriver = new AnnotationDriver(
            $reader,
            'Oro\Bundle\CalendarBundle\Entity'
        );

        $this->em = $this->_getTestEntityManager();
        $this->em->getConfiguration()->setMetadataDriverImpl($metadataDriver);
        $this->em->getConfiguration()->setEntityNamespaces(
            array(
                'OroCalendarBundle' => 'Oro\Bundle\CalendarBundle\Entity'
            )
        );
    }

    public function testGetEventListQueryBuilder()
    {
        /** @var CalendarEventRepository $repo */
        $repo = $this->em->getRepository('OroCalendarBundle:CalendarEvent');

        $qb = $repo->getEventListQueryBuilder(1, new \DateTime(), new \DateTime(), true);

        $this->assertEquals(
            'SELECT c.id as calendar, e.id, e.title, e.start, e.end, e.allDay, e.reminder'
            . ' FROM Oro\Bundle\CalendarBundle\Entity\CalendarEvent e'
            . ' INNER JOIN e.calendar c'
            . ' WHERE (e.start >= :start AND e.end < :end) AND'
            . ' c.id IN('
            . 'SELECT ac.id'
            . ' FROM Oro\Bundle\CalendarBundle\Entity\Calendar c1'
            . ' INNER JOIN c1.connections a'
            . ' INNER JOIN a.connectedCalendar ac'
            . ' WHERE c1.id = :id'
            . ')'
            . ' ORDER BY c.id, e.start ASC',
            $qb->getQuery()->getDQL()
        );
    }

    public function testGetEventListQueryBuilderForOwnEventsOnly()
    {
        /** @var CalendarEventRepository $repo */
        $repo = $this->em->getRepository('OroCalendarBundle:CalendarEvent');

        $qb = $repo->getEventListQueryBuilder(1, new \DateTime(), new \DateTime(), false);

        $this->assertEquals(
            'SELECT c.id as calendar, e.id, e.title, e.start, e.end, e.allDay, e.reminder'
            . ' FROM Oro\Bundle\CalendarBundle\Entity\CalendarEvent e'
            . ' INNER JOIN e.calendar c'
            . ' WHERE (e.start >= :start AND e.end < :end) AND c.id = :id'
            . ' ORDER BY c.id, e.start ASC',
            $qb->getQuery()->getDQL()
        );
    }

    public function testGetEventsToRemindQueryBuilder()
    {
        /** @var CalendarEventRepository $repo */
        $repo = $this->em->getRepository('OroCalendarBundle:CalendarEvent');

        $qb = $repo->getEventsToRemindQueryBuilder(new \DateTime());

        $this->assertEquals(
            'SELECT e, c, u'
            . ' FROM Oro\Bundle\CalendarBundle\Entity\CalendarEvent e'
            . ' INNER JOIN e.calendar c'
            . ' INNER JOIN c.owner u'
            . ' WHERE e.remindAt <= :current AND e.start > :current AND e.reminded = :reminded',
            $qb->getQuery()->getDQL()
        );
    }
}
