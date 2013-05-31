<?php

namespace Oro\Bundle\AddressBundle\Tests\Unit\Provider\ImportExport;

use Oro\Bundle\AddressBundle\Provider\ImportExport;

class ManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ImportExport\WriterInterface
     */
    private $writer;

    /**
     * @var ImportExport\ReaderInterface
     */
    private $reader;

    /**
     * Environment setup
     */
    public function setUp()
    {
        $this->writer = $this->getMock('Oro\Bundle\AddressBundle\Provider\ImportExport\WriterInterface');
        $this->reader = $this->getMock('Oro\Bundle\AddressBundle\Provider\ImportExport\ReaderInterface');
    }

    /**
     * Test sync with reader and writer
     */
    public function testSync()
    {
        $countryMock = $this->getMockBuilder('Oro\Bundle\AddressBundle\Entity\Country')->disableOriginalConstructor()->getMock();

        $this->reader
            ->expects($this->exactly(2))
            ->method('readBatch')
            ->will(
                $this->onConsecutiveCalls(
                    $countryMock, // first batch return
                    false // second batch return
                )
            );

        $this->writer
            ->expects($this->once())
            ->method('writeBatch')
            ->with($this->equalTo($countryMock))
            ->will($this->returnValue(true));

        /**
         * @var ImportExport\Manager
         */
        $manager = new ImportExport\Manager($this->writer, $this->reader);
        $manager->sync();
    }

    /**
     * Test sync for array data without reader
     */
    public function testArraySync()
    {
        $countryMock = $this->getMockBuilder('Oro\Bundle\AddressBundle\Entity\Country')->disableOriginalConstructor()->getMock();
        $data = array($countryMock);

        $this->writer
            ->expects($this->once())
            ->method('writeBatch')
            ->with($this->equalTo($data))
            ->will($this->returnValue(true));

        /**
         * @var ImportExport\Manager
         */
        $manager = new ImportExport\Manager($this->writer);
        $manager->sync($data);
    }

    /**
     * Test exception
     *
     * @expectedException \Exception
     */
    public function testExceptionSync()
    {
        /**
         * @var ImportExport\Manager
         */
        $manager = new ImportExport\Manager($this->writer);
        $manager->sync();
    }
}
