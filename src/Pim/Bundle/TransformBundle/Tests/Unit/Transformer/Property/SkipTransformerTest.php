<?php

namespace Pim\Bundle\TransformBundle\Tests\Unit\Transformer\Property;

use Pim\Bundle\TransformBundle\Transformer\Property\SkipTransformer;

/**
 * Tests related class
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SkipTransformerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test related method
     */
    public function testTransform()
    {
        $transformer = new SkipTransformer();
        $this->assertEquals(null, $transformer->transform(''));
    }
}
