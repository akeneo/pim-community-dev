<?php

namespace Oro\Bundle\EntityConfigBundle\Tests\Unit;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\EntityManager;
use Doctrine\Tests\OrmTestCase;

abstract class AbstractEntityManagerTest extends OrmTestCase
{
    /**
     * @var EntityManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $em;

    protected function setUp()
    {
        $reader = new AnnotationReader();

        $metadataDriver = new AnnotationDriver(
            $reader,
            __DIR__ . '/Fixture'
        );

        $this->em = $this->_getTestEntityManager();
        $this->em->getConfiguration()->setEntityNamespaces(array(
            'OroEntityConfigBundle' => 'Oro\\Bundle\\EntityConfigBundle\\Entity',
            'Fixture'               => 'Oro\\Bundle\\EntityConfigBundle\\Tests\\Unit\\Fixture'
        ));
        $this->em->getConfiguration()->setMetadataDriverImpl($metadataDriver);

        $schema = $this->getMockBuilder('Doctrine\Tests\Mocks\SchemaManagerMock')
            ->disableOriginalConstructor()
            ->getMock();

        $schema->expects($this->any())->method('listTableNames')->will($this->returnValue(array('oro_config_entity')));
        $this->em->getConnection()->getDriver()->setSchemaManager($schema);
    }
}
