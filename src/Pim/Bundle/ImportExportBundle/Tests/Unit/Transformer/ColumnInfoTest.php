<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Transformer;

use Pim\Bundle\ImportExportBundle\Transformer\ColumnInfo;

/**
 * Tests related class
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ColumnInfoTest extends \PHPUnit_Framework_TestCase
{
    public function getConstructData()
    {
        return array(
            'simple'        => array('name', 'name', 'name'),
            'underscored'   => array('property_name', 'property_name', 'propertyName'),
            'suffixed'      => array(
                'property_name-scope-locale',
                'property_name',
                'propertyName',
                array('scope', 'locale')
            )
        );
    }

    /**
     * @dataProvider getConstructData
     */
    public function testConstruct($label, $expectedName, $expectedPropertyPath, array $expectedSuffixes = array())
    {
        $info = new ColumnInfo($label);
        $this->assertEquals($label, $info->getLabel());
        $this->assertEquals($expectedName, $info->getName());
        $this->assertEquals($expectedPropertyPath, $info->getPropertyPath());
        $this->assertEquals($expectedSuffixes, $info->getSuffixes());
    }

    public function getSetAttributeData()
    {
        return array(
            'simple'                => array('label'),
            'with_locale'           => array('label-locale-scope', 'locale'),
            'with_scope'            => array('label-scope', null, 'scope'),
            'with_locale_and_scope' => array('label-locale-scope', 'locale', 'scope')
        );
    }

    /**
     * @dataProvider getSetAttributeData
     */
    public function testSetAttribute($label, $locale = null, $scope = null)
    {
        $info = new ColumnInfo($label);
        $attribute = $this->getAttributeMock(null !== $locale, null !== $scope);
        $info->setAttribute($attribute);
        $this->assertSame($attribute, $info->getAttribute());
        $this->assertEquals('backend_type', $info->getPropertyPath());
        $this->assertEquals($locale, $info->getLocale());
        $this->assertEquals($scope, $info->getScope());
    }

    /**
     * @expectedException \Pim\Bundle\ImportExportBundle\Exception\ColumnLabelException
     * @expectedExceptionMessage The column "label" must contain a locale code
     */
    public function testColumnWithoutLocalAndScope()
    {
        $info = new ColumnInfo('label');
        $attribute = $this->getAttributeMock(true, true);
        $info->setAttribute($attribute);
    }

    /**
     * @expectedException \Pim\Bundle\ImportExportBundle\Exception\ColumnLabelException
     * @expectedExceptionMessage The column "label" must contain a locale code
     */
    public function testColumnWithoutLocale()
    {
        $info = new ColumnInfo('label');
        $attribute = $this->getAttributeMock(true);
        $info->setAttribute($attribute);
    }

    /**
     * @expectedException \Pim\Bundle\ImportExportBundle\Exception\ColumnLabelException
     * @expectedExceptionMessage The column "label" must contain a scope code
     */
    public function testColumnWithoutScope()
    {
        $info = new ColumnInfo('label');
        $attribute = $this->getAttributeMock(false, true);
        $info->setAttribute($attribute);
    }

    /**
     * @expectedException \Pim\Bundle\ImportExportBundle\Exception\ColumnLabelException
     * @expectedExceptionMessage The column "label-locale" must contain a scope code
     */
    public function testTranslatableColumnWithoutScope()
    {
        $info = new ColumnInfo('label-locale');
        $attribute = $this->getAttributeMock(true, true);
        $info->setAttribute($attribute);
    }

    protected function getAttributeMock($translatable = false, $scopable = false)
    {
        $attribute = $this->getMock('Pim\Bundle\CatalogBundle\Entity\ProductAttribute');
        $attribute->expects($this->any())
            ->method('getTranslatable')
            ->will($this->returnValue($translatable));
        $attribute->expects($this->any())
            ->method('getScopable')
            ->will($this->returnValue($scopable));
        $attribute->expects($this->any())
            ->method('getBackendType')
            ->will($this->returnValue('backend_type'));

        return $attribute;
    }
}
