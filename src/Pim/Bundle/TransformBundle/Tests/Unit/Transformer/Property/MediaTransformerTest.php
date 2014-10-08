<?php

namespace Pim\Bundle\TransformBundle\Tests\Unit\Transformer\Property;

use Pim\Bundle\CatalogBundle\Model\ProductMedia;
use Pim\Bundle\TransformBundle\Transformer\Property\MediaTransformer;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Tests related class
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MediaTransformerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test related method
     */
    public function testTransform()
    {
        $transformer = new MediaTransformer('Pim\Bundle\CatalogBundle\Model\ProductMedia');
        $this->assertEquals(null, $transformer->transform(''));
        $this->assertEquals(null, $transformer->transform(' '));
        $d = tempnam('/tmp', 'pim-media-transformer-test');
        unlink($d);
        mkdir($d);
        $f = $d . '/file';
        touch($f);
        $this->assertEquals(null, $transformer->transform(' ' . $d . ' '));
        $this->assertEquals(new File($f), $transformer->transform(' ' . $f . ' '));
        unlink($f);
        rmdir($d);
    }

    /**
     * @return array
     */
    public function getUpdateProductValueData()
    {
        return array(
            'no_file_no_media' => array(false, false),
            'file_no_media' => array(true, false),
            'no_file_media' => array(false, true),
            'file_media' => array(true, true),
        );
    }

    /**
     * @param boolean $hasFile
     * @param boolean $mediaExists
     *
     * @dataProvider getUpdateProductValueData
     */
    public function testUpdateProductValue($hasFile, $mediaExists)
    {
        $f = tempnam('/tmp', 'pim-media-transformer-test');
        $this->media = $mediaExists ? new ProductMedia() : null;
        $file = $hasFile ? new File($f) : null;
        $transformer = new MediaTransformer('Pim\Bundle\CatalogBundle\Model\ProductMedia');
        $productValue = $this->getValue($hasFile, $mediaExists);
        $columnInfo = $this->getMock('Pim\Bundle\TransformBundle\Transformer\ColumnInfo\ColumnInfoInterface');
        $transformer->setValue($productValue, $columnInfo, $file);
        if ($hasFile) {
            $this->assertEquals($file, $this->media->getFile());
        }
        unlink($f);
    }
    /**
     * @expectedException \Pim\Bundle\TransformBundle\Exception\PropertyTransformerException
     * @expectedExceptionMessage File not found: "/bogus-file"
     */
    public function testUnvalid()
    {
        $transformer = new MediaTransformer('Pim\Bundle\CatalogBundle\Model\ProductMedia');
        $transformer->transform('/bogus-file');
    }

    protected function getValue($hasFile, $mediaExists)
    {
        $productValue = $this->getMockBuilder('Pim\Bundle\CatalogBundle\Model\ProductValueInterface')
            ->setMethods(
                [
                    'setText',
                    'setDatetime',
                    'setInteger',
                    'setId',
                    'getOption',
                    'getMedia',
                    'getDecimal',
                    'setDecimal',
                    'setAttribute',
                    'addOption',
                    'getBoolean',
                    'setOptions',
                    'setPrices',
                    'getId',
                    'setVarchar',
                    'setBoolean',
                    'getData',
                    'getMetric',
                    'getDate',
                    'getAttribute',
                    'getEntity',
                    'setMedia',
                    'getPrices',
                    'getOptions',
                    'getLocale',
                    'setMetric',
                    'addPrice',
                    'getVarchar',
                    'removePrice',
                    'hasData',
                    'setScope',
                    'removeOption',
                    'getText',
                    'setData',
                    'setOption',
                    'getPrice',
                    'setDate',
                    'addData',
                    'setLocale',
                    'isRemovable',
                    'getScope',
                    'getDatetime',
                    'setEntity',
                    'getInteger',
                    '__toString'
                ]
            )
            ->getMock();
        if ($hasFile) {
            $productValue
                ->expects($this->once())
                ->method('getMedia')
                ->will($this->returnValue($this->media));
            if (!$mediaExists) {
                $test = $this;
                $productValue
                    ->expects($this->once())
                    ->method('setMedia')
                    ->with($this->isInstanceOf('Pim\Bundle\CatalogBundle\Model\ProductMedia'))
                    ->will(
                        $this->returnCallback(
                            function ($createdMedia) use ($test) {
                                $test->media = $createdMedia;
                            }
                        )
                    );
            }
        } else {
            $productValue
                ->expects($this->never())
                ->method('getMedia');
        }

        return $productValue;
    }
}
