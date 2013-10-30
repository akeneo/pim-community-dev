<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Form\Transformer;
use Pim\Bundle\ImportExportBundle\Form\Transformer\ImportEntityModelTransformer;

/**
 * Tests corresponding class
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ImportEntityModelTransformerTest extends \PHPUnit_Framework_TestCase
{
    protected $entityCache;

    protected function setUp()
    {
        $this->entityCache = $this->getMockBuilder('Pim\Bundle\ImportExportBundle\Cache\EntityCache')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testTransform()
    {
        $transformer = new ImportEntityModelTransformer($this->entityCache, array());
        $this->assertEquals('', $transformer->transform('bogus'));
    }

    public function getTestData()
    {
        return array(
            'single'=>array('value', false),
            'multiple'=>array('value1,value2', true, 2)
        );
    }

    /**
     * @dataProvider getTestData
     */
    public function testReverseTransform($value, $multiple, $resultCount=false)
    {
        $transformer = new ImportEntityModelTransformer(
            $this->entityCache,
            array('class'=>'class', 'multiple'=>$multiple)
        );
        $return = new \stdClass;
        $this->entityCache
            ->expects($this->any())
            ->method('find')
            ->will($this->returnValue($return));
        $this->assertEquals(
            $multiple ? array_fill(0, $resultCount, $return) : $return,
            $transformer->reverseTransform($value)
        );
    }
}
