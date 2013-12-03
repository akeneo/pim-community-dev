<?php

namespace Oro\Bundle\DataAuditBundle\Tests\Unit\Metadata;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;

use Doctrine\Tests\Mocks\EntityManagerMock;
use Doctrine\Tests\OrmTestCase;

abstract class AbstractMetadataTest extends OrmTestCase
{
    /**
     * @var EntityManagerMock
     */
    protected $em;

    /**
     * @var \Oro\Bundle\DataAuditBundle\Metadata\Driver\AnnotationDriver
     */
    protected $loggableAnnotationDriver;

    public function setUp()
    {
        $reader = new AnnotationReader();

        $metadataDriver = new AnnotationDriver(
            $reader,
            'Oro\Bundle\DataAuditBundle\Tests\Unit\Fixture'
        );

        $this->em = $this->_getTestEntityManager();
        $this->em->getConfiguration()->setEntityNamespaces(array(
            'OroUserBundle'      => 'Oro\\Bundle\\UserBundle\\Entity',
            'OroDataAuditBundle' => 'Oro\\Bundle\\DataAuditBundle\\Entity'
        ));
        $this->em->getConfiguration()->setMetadataDriverImpl($metadataDriver);

        $this->loggableAnnotationDriver = new \Oro\Bundle\DataAuditBundle\Metadata\Driver\AnnotationDriver(
            new AnnotationReader()
        );
    }
}
