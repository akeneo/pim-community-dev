<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Converter;

use Pim\Bundle\ImportExportBundle\Converter\ProductFamilyConverter;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductFamilyConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $em = $this->getEntityManagerMock();
        $this->repository = $this->getRepositoryMock();
        $em->expects($this->any())
            ->method('getRepository')
            ->with('PimCatalogBundle:Family')
            ->will($this->returnValue($this->repository));

        $this->converter = new ProductFamilyConverter($em);
    }

    /**
     * Test related method
     */
    public function testConvertFamily()
    {
        $this->repository
            ->expects($this->any())
            ->method('findOneBy')
            ->with(array('code' => 'bike'))
            ->will($this->returnValue($this->getFamilyMock(1987)));

        $this->assertEquals(array('family' => 1987), $this->converter->convert(array('[family]' => 'bike')));
    }

    /**
     * Test related method
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Couldn't find a family with code "moto"
     */
    public function testConvertUnknownFamily()
    {
        $this->repository
            ->expects($this->any())
            ->method('findOneBy')
            ->with(array('code' => 'moto'))
            ->will($this->returnValue(null));

        $this->converter->convert(array('[family]' => 'moto'));
    }

    /**
     * Test related method
     */
    public function testUnresolvedFamilyKey()
    {
        $this->assertEquals(array(), $this->converter->convert(array('Family' => 'moto')));
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getEntityManagerMock()
    {
        return $this
            ->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getRepositoryMock()
    {
        return $this
            ->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @param integer $id
     *
     * @return \Pim\Bundle\CatalogBundle\Entity\Family
     */
    protected function getFamilyMock($id)
    {
        $family = $this->getMock('Pim\Bundle\CatalogBundle\Entity\Family');

        $family->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($id));

        return $family;
    }
}
