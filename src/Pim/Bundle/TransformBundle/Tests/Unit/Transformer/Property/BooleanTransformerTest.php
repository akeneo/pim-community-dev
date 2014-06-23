<?php

namespace Pim\Bundle\TransformBundle\Tests\Unit\Transformer\Property;

use Pim\Bundle\TransformBundle\Transformer\Property\BooleanTransformer;

/**
 * Tests related class
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BooleanTransformerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test related method
     */
    public function testTransform()
    {
        $transformer = new BooleanTransformer();

        $this->assertNull($transformer->transform(' '));

        $this->assertFalse($transformer->transform(0));
        $this->assertFalse($transformer->transform(false));
        $this->assertFalse($transformer->transform((float) 0));
        $this->assertFalse($transformer->transform('0'));
        $this->assertFalse($transformer->transform('false'));
        $this->assertFalse($transformer->transform('no'));

        $this->assertTrue($transformer->transform(1));
        $this->assertTrue($transformer->transform(true));
        $this->assertTrue($transformer->transform((float) 1));
        $this->assertTrue($transformer->transform('1'));
        $this->assertTrue($transformer->transform('true'));
        $this->assertTrue($transformer->transform('yes'));
    }

    public function testInvalidTransformation()
    {
        $object = new \stdClass();
        $this->setExpectedException(
            'Pim\Bundle\TransformBundle\Exception\PropertyTransformerException',
            'Cannot transform "stdClass" into boolean'
        );

        $transformer = new BooleanTransformer();
        $transformer->transform($object);
    }
}
