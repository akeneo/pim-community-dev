<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Converter;

use Pim\Bundle\ImportExportBundle\Converter\ProductFamilyConverter;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductFamilyConverterTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $em = $this->getEntityManagerMock();
        $this->repository = $this->getRepositoryMock();
        $em->expects($this->any())
            ->method('getRepository')
            ->with('PimProductBundle:Family')
            ->will($this->returnValue($this->repository));

        $this->converter = new ProductFamilyConverter($em);
    }

    public function testConvertFamily()
    {
        $this->repository
            ->expects($this->any())
            ->method('findOneBy')
            ->with(array('code' => 'bike'))
            ->will($this->returnValue($this->getFamilyMock(1987)));

        $this->assertEquals(array('family' => 1987), $this->converter->convert(array('[family]' => 'bike')));
    }

    public function testConvertUnknownFamily()
    {
        $this->repository
            ->expects($this->any())
            ->method('findOneBy')
            ->with(array('code' => 'moto'))
            ->will($this->returnValue(null));

        $this->assertEquals(array(), $this->converter->convert(array('[family]' => 'moto')));
    }

    public function testUnresolvedFamilyKey()
    {
        $this->assertEquals(array(), $this->converter->convert(array('Family' => 'moto')));
    }

    protected function getEntityManagerMock()
    {
        return $this
            ->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function getRepositoryMock()
    {
        return $this
            ->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function getFamilyMock($id)
    {
        $family = $this->getMock('Pim\Bundle\ProductBundle\Entity\Family');

        $family->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($id));

        return $family;
    }
}
