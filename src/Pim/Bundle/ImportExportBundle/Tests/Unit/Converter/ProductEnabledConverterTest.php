<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Converter;

use Pim\Bundle\ImportExportBundle\Converter\ProductEnabledConverter;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductEnabledConverterTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->converter = new ProductEnabledConverter();
    }

    public function testConvertProductEnabled()
    {
        $this->assertEquals(
            array('enabled' => true),
            $this->converter->convert(array(ProductEnabledConverter::ENABLED_KEY => true))
        );
    }

    public function testConvertProductDisabled()
    {
        $this->assertEquals(
            array('enabled' => false),
            $this->converter->convert(array(ProductEnabledConverter::ENABLED_KEY => false))
        );
    }

    public function testUnresolvedEnabledKey()
    {
        $this->assertEquals(array(), $this->converter->convert(array('Enabled' => true)));
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
        $family = $this->getMock('Pim\Bundle\CatalogBundle\Entity\Family');

        $family->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($id));

        return $family;
    }
}
