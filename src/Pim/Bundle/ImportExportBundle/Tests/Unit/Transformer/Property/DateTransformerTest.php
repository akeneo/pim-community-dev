<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Transformer\Property;

use Pim\Bundle\ImportExportBundle\Transformer\Property\DateTransformer;

/**
 * Tests related class
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DateTransformerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test related method
     */
    public function testTransform()
    {
        $transformer = new DateTransformer();
        $this->assertEquals(null, $transformer->transform(''));
        $this->assertEquals(null, $transformer->transform(' '));
        $this->assertEquals(new \DateTime('2012-07-10'), $transformer->transform(' 2012-07-10 '));
    }

    /**
     * @expectedException Pim\Bundle\ImportExportBundle\Exception\PropertyTransformerException
     * @expectedExceptionMessage Invalid date
     */
    public function testTransformerException()
    {
        $transformer = new DateTransformer();
        $transformer->transform('BOGUS');
    }
}
