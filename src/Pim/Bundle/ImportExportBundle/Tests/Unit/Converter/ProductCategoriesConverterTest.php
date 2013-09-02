<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Converter;

use Pim\Bundle\ImportExportBundle\Converter\ProductCategoriesConverter;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductCategoriesConverterTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $em = $this->getEntityManagerMock();
        $this->repository = $this->getRepositoryMock();
        $em->expects($this->any())
            ->method('getRepository')
            ->with('PimCatalogBundle:Category')
            ->will($this->returnValue($this->repository));

        $this->converter = new ProductCategoriesConverter($em);
    }

    public function testConvertCategories()
    {
        $this->repository
            ->expects($this->any())
            ->method('findOneBy')
            ->will(
                $this->returnValueMap(
                    array(
                        array(array('code' => 'shoe'), null, $this->getCategoryMock(16)),
                        array(array('code' => 'hat'), null, $this->getCategoryMock(23)),
                        array(array('code' => 'glasses'), null, $this->getCategoryMock(42)),
                    )
                )
            );

        $this->assertEquals(
            array('categories' => array(16, 23, 42)),
            $this->converter->convert(array('[categories]' => 'shoe,hat,glasses'))
        );
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

    protected function getCategoryMock($id)
    {
        $category = $this->getMock('Pim\Bundle\CatalogBundle\Entity\Category');

        $category->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($id));

        return $category;
    }
}
