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
        $this->assertEquals(true, $transformer->transform(1));
        $this->assertEquals(false, $transformer->transform(0));
        $this->assertEquals(true, $transformer->transform((float) 1));
        $this->assertEquals(false, $transformer->transform((float) 0));
        $this->assertEquals(true, $transformer->transform(true));
        $this->assertEquals(false, $transformer->transform(false));
        $this->assertEquals(true, $transformer->transform('1'));
        $this->assertEquals(false, $transformer->transform('0'));
    }
}
