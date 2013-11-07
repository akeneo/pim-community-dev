<?php

namespace Oro\Bundle\CalendarBundle\Tests\Unit\Entity\Repository;

use Doctrine\Tests\OrmTestCase;
use Doctrine\Tests\Mocks\EntityManagerMock;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Oro\Bundle\CalendarBundle\Entity\Repository\CalendarConnectionRepository;

class CalendarConnectionRepositoryTest extends OrmTestCase
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

    public function testGetConnectionListQueryBuilder()
    {
        /** @var CalendarConnectionRepository $repo */
        $repo = $this->em->getRepository('OroCalendarBundle:CalendarConnection');

        $qb = $repo->getConnectionListQueryBuilder(1);

        $this->assertEquals(
            'SELECT a.color, a.backgroundColor'
            . ', ac.id as calendar, ac.name as calendarName'
            . ', u.id as owner, u.firstName as ownerFirstName, u.lastName as ownerLastName'
            . ' FROM Oro\Bundle\CalendarBundle\Entity\CalendarConnection a'
            . ' INNER JOIN a.calendar c'
            . ' INNER JOIN a.connectedCalendar ac'
            . ' INNER JOIN ac.owner u'
            . ' WHERE c.id = :id ORDER BY a.createdAt ASC',
            $qb->getQuery()->getDQL()
        );
    }
}
