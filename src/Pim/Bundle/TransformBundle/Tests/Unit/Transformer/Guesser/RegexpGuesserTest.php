<?php

namespace Pim\Bundle\TransformBundle\Tests\Unit\Transformer\Guesser;

use Pim\Bundle\TransformBundle\Transformer\Guesser\RegexpGuesser;

/**
 * Tests related class
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RegexpGuesserTest extends GuesserTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->columnInfo->expects($this->any())
            ->method('getLabel')
            ->will($this->returnValue('column_label'));
    }

    public function testMatching()
    {
        $guesser = new RegexpGuesser($this->transformer, 'class', ['/bogus/', '/^column_label$/']);
        $this->assertEquals(
            [$this->transformer, []],
            $guesser->getTransformerInfo($this->columnInfo, $this->metadata)
        );
    }

    public function testNotClass()
    {
        $guesser = new RegexpGuesser($this->transformer, 'other_class', ['/bogus/', '/^column_label$/']);
        $this->assertNull($guesser->getTransformerInfo($this->columnInfo, $this->metadata));
    }

    public function testNotMatching()
    {
        $guesser = new RegexpGuesser($this->transformer, 'class', ['/bogus/']);
        $this->assertNull($guesser->getTransformerInfo($this->columnInfo, $this->metadata));
    }
}
