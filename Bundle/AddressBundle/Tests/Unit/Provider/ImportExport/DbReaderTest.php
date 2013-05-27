<?php

namespace Oro\Bundle\AddressBundle\Tests\Unit\Provider\ImportExport;

use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Provider\ImportExport;
use Doctrine\Common\Persistence\ObjectManager;

class DbReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @var ReaderInterface
     */
    private $reader;

    /**
     * @var string
     */
    private $class;

    /**
     * @var int
     */
    private $batchSize = 50;

    /**
     * Environment setup
     */
    public function setUp()
    {
        $this->class = 'Oro\Bundle\AddressBundle\Entity\Country';
        $this->om = $this->getMock('Doctrine\Common\Persistence\ObjectManager');

        $classMetaData = $this->getMockBuilder('Doctrine\ORM\Mapping\ClassMetadata')
            ->disableOriginalConstructor()
            ->getMock();

        $classMetaData
            ->expects($this->once())
            ->method('getName')
            ->with()
            ->will($this->returnValue($this->class));

        $this->om
            ->expects($this->once())
            ->method('getClassMetadata')
            ->with($this->equalTo($this->class))
            ->will($this->returnValue($classMetaData));


        $this->reader = new ImportExport\DbReader($this->class, $this->om, $this->batchSize);
    }

    /**
     * Test setting limits
     */
    public function testSettingBatchLimit()
    {
                $this->assertEquals($this->batchSize, $this->reader->getBatchSize());
    }

    /**
     * Test read batch
     */
    public function testDbReader()
    {
        $repository = $this->getMock('Doctrine\Common\Persistence\ObjectRepository');
        $repository
            ->expects($this->exactly(3))
            ->method('findBy')
            ->with(
                $this->equalTo(array()),
                $this->equalTo(array()),
                $this->batchSize,
                $this->logicalOr(0, $this->batchSize)
            )
            ->will(
                $this->returnValue(
                    array(
                        new Country(),
                    )
                )
            );

        $this->om
            ->expects($this->any())
            ->method('getRepository')
            ->with($this->equalTo($this->class))
            ->will($this->returnValue($repository));

        $this->reader->readBatch();
        $this->reader->readBatch();

        $this->reader->reset();
        $this->reader->readBatch();
    }
}
