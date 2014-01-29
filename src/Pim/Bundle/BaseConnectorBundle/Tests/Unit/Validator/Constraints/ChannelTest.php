<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Validator\Constraints;

use Pim\Bundle\ImportExportBundle\Validator\Constraints\Channel;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChannelTest extends \PHPUnit_Framework_TestCase
{
    protected $constraint;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->constraint = new Channel();
    }

    /**
     * Test related method
     */
    public function testExtendsChoiceConstraint()
    {
        $this->assertInstanceOf('Symfony\Component\Validator\Constraints\Choice', $this->constraint);
    }

    /**
     * Test related method
     */
    public function testMessage()
    {
        $this->assertEquals('The channel you selected does not exist.', $this->constraint->message);
    }

    public function testValidatedBy()
    {
        $this->assertInternalType('string', $this->constraint->validatedBy());
    }
}
