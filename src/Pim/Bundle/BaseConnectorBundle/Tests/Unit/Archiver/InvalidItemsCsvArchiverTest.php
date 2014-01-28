<?php

namespace Pim\Bundle\BaseConnectorBundle\Tests\Unit\Archiver;

use Pim\Bundle\BaseConnectorBundle\Archiver\InvalidItemsCsvArchiver;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InvalidItemsCsvArchiverTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->collector  = $this->getInvalidItemsCollectorMock();
        $this->encoder    = $this->getCsvEncoderMock();
        $this->filesystem = $this->getFilesystemMock();
        $this->archiver   = new InvalidItemsCsvArchiver($this->collector, $this->encoder, $this->filesystem);
    }

    public function testIsAnArchiver()
    {
        $this->assertInstanceOf('Pim\Bundle\BaseConnectorBundle\Archiver\ArchiverInterface', $this->archiver);
    }

    public function testGetName()
    {
        $this->assertSame('invalid', $this->archiver->getName());
    }

    public function testDoNothingWhenNoInvalidItemsCollected()
    {
        $this->collector
            ->expects($this->any())
            ->method('getInvalidItems')
            ->will($this->returnValue(array()));

        $jobExecution = $this->getJobExecutionMock(
            $this->getJobInstanceMock('import', 'product_import'),
            42
        );

        $this->filesystem
            ->expects($this->never())
            ->method('write');

        $this->archiver->archive($jobExecution, $this->filesystem);
    }

    public function testArchiveInvalidItems()
    {
        $this->collector
            ->expects($this->any())
            ->method('getInvalidItems')
            ->will($this->returnValue(array('item1', 'item2')));

        $this->archiver->setHeader(array('sku', 'name', 'description'));

        $this->encoder
            ->expects($this->any())
            ->method('encode')
            ->will(
                $this->returnValueMap(
                    array(
                        array(array('sku', 'name', 'description'), 'csv', array(), 'sku;name;description'),
                        array('item1',                             'csv', array(), 'foo;"Teh Foo"'),
                        array('item2',                             'csv', array(), 'bar;"Teh Bar";"Teh Bar Descr"'),
                    )
                )
            );

        $jobExecution = $this->getJobExecutionMock(
            $this->getJobInstanceMock('import', 'product_import'),
            42
        );

        $this->filesystem
            ->expects($this->once())
            ->method('write')
            ->with(
                'import/product_import/42/invalid/invalid_items.csv',
                "sku;name;descriptionfoo;\"Teh Foo\"bar;\"Teh Bar\";\"Teh Bar Descr\""
            );

        $this->archiver->archive($jobExecution);
    }

    public function testGetArchives()
    {
        $this->filesystem
            ->expects($this->any())
            ->method('listKeys')
            ->will($this->returnValue(array('keys' => array('foo/fooFile.txt','bar/barFile.txt'))));

        $jobExecution = $this->getJobExecutionMock(
            $this->getJobInstanceMock('import', 'product_import', null),
            42
        );
        $this->assertSame(
            array(
                'fooFile.txt' => 'foo/fooFile.txt',
                'barFile.txt' => 'bar/barFile.txt'
            ),
            $this->archiver->getArchives($jobExecution)
        );
    }

    protected function getInvalidItemsCollectorMock()
    {
        return $this->getMock('Pim\Bundle\BaseConnectorBundle\EventListener\InvalidItemsCollector');
    }

    protected function getCsvEncoderMock()
    {
        return $this->getMock('Pim\Bundle\TransformBundle\Encoder\CsvEncoder');
    }

    protected function getJobExecutionMock($jobInstance, $id)
    {
        $jobExecution = $this
            ->getMockBuilder('Oro\Bundle\BatchBundle\Entity\JobExecution')
            ->disableOriginalConstructor()
            ->getMock();

        $jobExecution->expects($this->any())
            ->method('getJobInstance')
            ->will($this->returnValue($jobInstance));

        $jobExecution->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($id));

        return $jobExecution;
    }

    protected function getJobInstanceMock($type, $alias)
    {
        $jobInstance = $this
            ->getMockBuilder('Oro\Bundle\BatchBundle\Entity\JobInstance')
            ->disableOriginalConstructor()
            ->getMock();

        $jobInstance->expects($this->any())->method('getType')->will($this->returnValue($type));
        $jobInstance->expects($this->any())->method('getAlias')->will($this->returnValue($alias));

        return $jobInstance;
    }

    protected function getFilesystemMock()
    {
        return $this
            ->getMockBuilder('Gaufrette\Filesystem')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
