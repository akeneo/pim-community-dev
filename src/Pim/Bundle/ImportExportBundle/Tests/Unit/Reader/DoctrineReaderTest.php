<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Reader;

use Pim\Bundle\ImportExportBundle\Reader\DoctrineReader;

/**
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DoctrineReaderTest extends \PHPUnit_Framework_TestCase
{
    public function testReadUsingFindAll()
    {
        $em         = $this->getEntityManagerMock();
        $repository = $this->getRepositoryMock();
        $em->expects($this->any())
            ->method('getRepository')
            ->with('App:foo')
            ->will($this->returnValue($repository));

        $reader = new DoctrineReader($em, 'App:foo');

        $repository->expects($this->once())
            ->method('findAll')
            ->will($this->returnValue(array('foo','bar')));

        $this->assertEquals(array('foo','bar'), $reader->read());
    }

    public function testUsingCustomMethodAndParameters()
    {
        $em         = $this->getEntityManagerMock();
        $repository = $this->getRepositoryMock();
        $em->expects($this->any())
            ->method('getRepository')
            ->with('App:foo')
            ->will($this->returnValue($repository));

        $reader = new DoctrineReader($em, 'App:foo', 'findBy', array(array('author' => 'me')));

        $repository->expects($this->never())
            ->method('findAll');

        $repository->expects($this->once())
            ->method('findBy')
            ->with(array('author' => 'me'))
            ->will($this->returnValue(array('buz','boz')));

        $this->assertEquals(array('buz','boz'), $reader->read());
    }

    private function getEntityManagerMock()
    {
        return $this
            ->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function getRepositoryMock()
    {
        return $this
            ->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
    }
}

