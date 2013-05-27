<?php
namespace Oro\Bundle\FlexibleEntityBundle\Tests\Unit\AttributeType;

use Oro\Bundle\FlexibleEntityBundle\AttributeType\EmailType;

class EmailTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test getter
     *
     */
    public function testGetter()
    {
        $backend = 'varchar';
        $attType = new EmailType($backend, 'test');
        $this->assertEquals($attType->getName(), 'oro_flexibleentity_email');
        $this->assertEquals($attType->getBackendType(), $backend);
        $this->assertEquals($attType->getFormType(), 'test');
    }
}
