<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Transformer\Property;

use Pim\Bundle\ImportExportBundle\Transformer\Property\MetricTransformer;
use Oro\Bundle\FlexibleEntityBundle\Entity\Metric;

/**
 * Tests related class
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MetricTransformerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test related method
     */
    public function testTransform()
    {
        $transformer = new MetricTransformer();
        $this->assertEquals(null, $transformer->transform(''));
        $this->assertEquals(null, $transformer->transform(' '));
        $m = new Metric();
        $m->setData(15.2);
        $m->setUnit('KILOGRAM');
        $this->assertEquals($m, $transformer->transform('15.2 KILOGRAM'));
    }

    /**
     * @expectedException Pim\Bundle\ImportExportBundle\Exception\PropertyTransformerException
     * @expectedExceptionMessage Malformed metric: 15.2
     */
    public function testUnvalidTransform()
    {
        $transformer = new MetricTransformer();
        $transformer->transform('15.2');
    }
}
