<?php

namespace Pim\Bundle\ProductBundle\Tests\Unit\Form\DataTransformer;

use Pim\Bundle\ProductBundle\Form\DataTransformer\BooleanToStringTransformer;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BooleanToStringTransformerTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->transformer = new BooleanToStringTransformer;
    }

    public function testTransform()
    {
        $this->assertEquals('0', $this->transformer->transform(false));
        $this->assertEquals('1', $this->transformer->transform(true));
    }

    public function testReverseTransform()
    {
        $this->assertEquals(false, $this->transformer->reverseTransform(''));
        $this->assertEquals(false, $this->transformer->reverseTransform('0'));
        $this->assertEquals(false, $this->transformer->reverseTransform(0));
        $this->assertEquals(true, $this->transformer->reverseTransform('1'));
        $this->assertEquals(true, $this->transformer->reverseTransform(1));
    }

    /**
     * @expectedException Symfony\Component\Form\Exception\TransformationFailedException
     */
    public function testInvalidTransform()
    {
        $this->transformer->transform('something is wrong.');
    }

    /**
     * @expectedException Symfony\Component\Form\Exception\TransformationFailedException
     */
    public function testInvalidReverseTransform()
    {
        $this->transformer->reverseTransform('something is wrong.');
    }
}
