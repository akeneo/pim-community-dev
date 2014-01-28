<?php

namespace Pim\Bundle\BaseConnectorBundle\Tests\Unit\Reader\File;

use Pim\Bundle\BaseConnectorBundle\Reader\File\CsvProductReader;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CsvProductReaderTest extends CsvReaderTest
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->archiver = $this->getArchiverMock();
        $this->reader = new CsvProductReader(
            $this->archiver,
            $this->getEntityManagerMock(
                array('sku', 'name'),
                array('view', 'manual')
            ),
            'Pim\Bundle\CatalogBundle\Entity\Attribute'
        );
        $this->stepExecution = $this->getStepExecutionMock();
        $this->reader->setStepExecution($this->stepExecution);
    }

    /**
     * Test related method
     */
    public function testExtendsCsvReader()
    {
        $this->assertInstanceOf('Pim\Bundle\BaseConnectorBundle\Reader\File\CsvReader', $this->reader);
    }

    /**
     * Test related method
     */
    public function testInitializeWithAttributeRepository()
    {
        $this->assertAttributeEquals(
            array('view', 'manual'),
            'mediaAttributes',
            $this->reader
        );
    }

    /**
     * Test related method
     */
    public function testMediaPathAreAbsolute()
    {
        $this->reader->setFilePath(__DIR__ . '/../../../fixtures/with_media.csv');

        $this->assertEquals(
            array(
                'sku'          => 'SKU-001',
                'name'         => 'door',
                'view'         => __DIR__ . '/../../../fixtures/sku-001.jpg',
                'manual-fr_FR' => __DIR__ . '/../../../fixtures/sku-001.txt',
            ),
            $this->reader->read()
        );
    }

    /**
     * @param array $uniqueAttributeCodes
     * @param array $mediaAttributeCodes
     *
     * @return Doctrine\ORM\EntityManager
     */
    protected function getEntityManagerMock(array $uniqueAttributeCodes, array $mediaAttributeCodes)
    {
        $em = $this
            ->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $em->expects($this->any())
            ->method('getRepository')
            ->will(
                $this->returnValue(
                    $this->getEntityRepositoryMock(
                        $uniqueAttributeCodes,
                        $mediaAttributeCodes
                    )
                )
            );

        return $em;
    }

    /**
     * @param array $uniqueAttributeCodes
     * @param array $mediaAttributeCodes
     *
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getEntityRepositoryMock(array $uniqueAttributeCodes, array $mediaAttributeCodes)
    {
        $repository = $this
            ->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->setMethods(array('findUniqueAttributeCodes', 'findMediaAttributeCodes'))
            ->disableOriginalConstructor()
            ->getMock();

        $repository->expects($this->any())
            ->method('findUniqueAttributeCodes')
            ->will($this->returnValue($uniqueAttributeCodes));

        $repository->expects($this->any())
            ->method('findMediaAttributeCodes')
            ->will($this->returnValue($mediaAttributeCodes));

        return $repository;
    }

    protected function getArchiverMock()
    {
        return $this->getMockBuilder('Pim\Bundle\BaseConnectorBundle\Archiver\InvalidItemsCsvArchiver')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
