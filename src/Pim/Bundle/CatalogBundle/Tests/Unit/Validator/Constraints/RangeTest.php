<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\Validator\Constraints;

use Pim\Bundle\CatalogBundle\Validator\Constraints\Range;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RangeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->target = new Range(['min' => 0]);
    }

    /**
     * Test related method
     */
    public function testExtendsRangeConstraint()
    {
        $this->assertInstanceOf('Symfony\Component\Validator\Constraints\Range', $this->target);
    }

    /**
     * Test related method
     */
    public function testMinDateMessage()
    {
        $this->assertEquals('This date should be {{ limit }} or after.', $this->target->minDateMessage);
    }

    /**
     * Test related method
     */
    public function testMaxDateMessage()
    {
        $this->assertEquals('This date should be {{ limit }} or before.', $this->target->maxDateMessage);
    }

    /**
     * Test related method
     */
    public function testValidDateMessage()
    {
        $this->assertEquals('This value is not a valid date.', $this->target->invalidDateMessage);
    }
}
