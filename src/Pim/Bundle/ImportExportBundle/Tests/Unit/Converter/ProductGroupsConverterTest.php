<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Converter;

use Pim\Bundle\ImportExportBundle\Converter\ProductGroupsConverter;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductGroupsConverterTest extends \PHPUnit_Framework_TestCase
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
            ->with('PimCatalogBundle:Group')
            ->will($this->returnValue($this->repository));

        $this->converter = new ProductGroupsConverter($em);
    }

    /**
     * Test related method
     */
    public function testConvertGroups()
    {
        $this->repository
            ->expects($this->any())
            ->method('findOneBy')
            ->will(
                $this->returnValueMap(
                    array(
                        array(array('code' => 'xsell'), null, $this->getGroupMock(16)),
                        array(array('code' => 'cross'), null, $this->getGroupMock(23)),
                        array(array('code' => 'related'), null, $this->getGroupMock(42)),
                    )
                )
            );

        $this->assertEquals(
            array('groups' => array(23, 42, 16)),
            $this->converter->convert(array('[groups]' => 'cross,related,xsell'))
        );
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
     * @return \Pim\Bundle\CatalogBundle\Entity\Group
     */
    protected function getGroupMock($id)
    {
        $group = $this->getMock('Pim\Bundle\CatalogBundle\Entity\Group');

        $group->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($id));

        return $group;
    }
}
