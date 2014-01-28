<?php

namespace Pim\Bundle\TransformBundle\Tests\Unit\Transformer\Guesser;

use Pim\Bundle\TransformBundle\Transformer\Guesser\DefaultGuesser;

/**
 * Tests related class
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DefaultGuesserTest extends GuesserTestCase
{
    protected $guesser;

    protected function setUp()
    {
        parent::setUp();
        $this->guesser = new DefaultGuesser($this->transformer);
    }

    public function testMatching()
    {
        $this->metadata->expects($this->once())
            ->method('hasField')
            ->with($this->equalTo('property_path'))
            ->will($this->returnValue(true));

        $this->assertEquals(
            array($this->transformer, array()),
            $this->guesser->getTransformerInfo($this->columnInfo, $this->metadata)
        );
    }

    public function testNoField()
    {
        $this->metadata->expects($this->once())
            ->method('hasField')
            ->with($this->equalTo('property_path'))
            ->will($this->returnValue(false));

        $this->assertNull($this->guesser->getTransformerInfo($this->columnInfo, $this->metadata));
    }
}
