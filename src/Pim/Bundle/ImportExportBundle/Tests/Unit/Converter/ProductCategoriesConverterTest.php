<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Converter;

use Pim\Bundle\ImportExportBundle\Converter\ProductCategoriesConverter;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductCategoriesConverterTest extends \PHPUnit_Framework_TestCase
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
            ->with('PimCatalogBundle:Category')
            ->will($this->returnValue($this->repository));

        $this->converter = new ProductCategoriesConverter($em);
    }

    /**
     * Test related method
     */
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

    /**
     * Test related method
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Couldn't find a category with code "vehicle"
     */
    public function testConvertUnknownCategory()
    {
        $this->repository
            ->expects($this->any())
            ->method('findOneBy')
            ->with(array('code' => 'vehicle'))
            ->will($this->returnValue(null));

        $this->converter->convert(array('[categories]' => 'vehicle'));
    }

    /**
     * Test related method
     */
    public function testUnresolvedCategoriesKey()
    {
        $this->assertEquals(array(), $this->converter->convert(array('Categories' => 'vehicle,2-wheels')));
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
     * @return \Pim\Bundle\CatalogBundle\Entity\Category
     */
    protected function getCategoryMock($id)
    {
        $category = $this->getMock('Pim\Bundle\CatalogBundle\Entity\Category');

        $category->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($id));

        return $category;
    }
}
