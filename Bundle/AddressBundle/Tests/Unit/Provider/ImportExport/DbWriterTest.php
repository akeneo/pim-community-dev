<?php

namespace Oro\Bundle\AddressBundle\Tests\Unit\Provider\ImportExport;

use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Provider\ImportExport;
use Doctrine\Common\Persistence\ObjectManager;

class DbWriterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @var ImportExport\WriterInterface
     */
    private $writer;

    /**
     * Environment setup
     */
    public function setUp()
    {
        $this->om = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $this->writer = new ImportExport\DbWriter($this->om);
    }

    /**
     * Test write batch
     */
    public function testWriter()
    {
        $data =  array(
            new Country('Ukraine', 'UA', 'UKR'),
            new Country('United States of America', 'US', 'USA'),
            new Country('Russian Federation', 'RU', 'RUS'),
        );

        $this->om
            ->expects($this->exactly(count($data)))
            ->method('persist')
            ->with($this->containsOnlyInstancesOf('Country'));

        $this->om
            ->expects($this->once())
            ->method('flush');

        $this->writer->writeBatch($data);
    }

    /**
     * Test exception on not valid write
     *
     * @expectedException \Exception
     */
    public function testEmptyWrite()
    {
        $this->assertFalse($this->writer->writeBatch(array()));

        $this->writer->writeBatch(array(1));
    }
}
