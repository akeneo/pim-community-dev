<?php

namespace Pim\Bundle\TransformBundle\Tests\Unit\Transformer\Property;

use Akeneo\Component\FileStorage\Model\File;
use Pim\Bundle\TransformBundle\Transformer\Property\MediaTransformer;

/**
 * Tests related class
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MediaTransformerTest extends \PHPUnit_Framework_TestCase
{
    protected $fileStorer;

    protected $media;

    /**
     * Test related method
     */
    public function testTransform()
    {
        $transformer = new MediaTransformer($this->fileStorer);
        $this->assertEquals(null, $transformer->transform(''));
        $this->assertEquals(null, $transformer->transform(' '));
        $d = tempnam('/tmp', 'pim-media-transformer-test');
        unlink($d);
        mkdir($d);
        $f = $d . '/file';
        touch($f);
        $this->assertEquals(null, $transformer->transform(' ' . $d . ' '));
        $this->assertEquals(new File(), $transformer->transform(' ' . $f . ' '));
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
     * @param bool $hasFile
     * @param bool $mediaExists
     *
     * @dataProvider getUpdateProductValueData
     */
    public function testUpdateProductValue($hasFile, $mediaExists)
    {
        $f = tempnam('/tmp', 'pim-media-transformer-test');
        $this->media = $mediaExists ? new File() : null;
        $file = $hasFile ? new File() : null;
        $transformer = new MediaTransformer($this->fileStorer);
        $productValue = $this->getValue($hasFile, $mediaExists);
        $columnInfo = $this->getMock('Pim\Bundle\TransformBundle\Transformer\ColumnInfo\ColumnInfoInterface');
        $transformer->setValue($productValue, $columnInfo, $file);
        if ($hasFile) {
            $this->assertEquals($file, $this->media);
        }
        unlink($f);
    }
    /**
     * @expectedException \Pim\Bundle\TransformBundle\Exception\PropertyTransformerException
     * @expectedExceptionMessage File not found: "/bogus-file"
     */
    public function testUnvalid()
    {
        $transformer = new MediaTransformer($this->fileStorer);
        $transformer->transform('/bogus-file');
    }

    protected function setUp()
    {
        $this->fileStorer = $this
            ->getMockBuilder('Akeneo\Component\FileStorage\RawFile\RawFileStorer')
            ->disableOriginalConstructor()
            ->setMethods(['store'])
            ->getMock()
        ;

        $this->fileStorer
            ->expects($this->any())
            ->method('store')
            ->will($this->returnValue(new File()))
        ;
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
                    'getProduct',
                    'setProduct',
                    '__toString'
                ]
            )
            ->getMock();
        if ($hasFile) {
            if (!$mediaExists) {
                $test = $this;
                $productValue
                    ->expects($this->once())
                    ->method('setMedia')
                    ->with($this->isInstanceOf('Akeneo\Component\FileStorage\Model\FileInterface'))
                    ->will(
                        $this->returnCallback(
                            function ($createdMedia) use ($test) {
                                $test->media = $createdMedia;
                            }
                        )
                    );
            }
        }

        return $productValue;
    }
}
