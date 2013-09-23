<?php

namespace Oro\Bundle\EmailBundle\Tests\Unit\Entity\Util;

use Oro\Bundle\EmailBundle\Entity\Util\EmailUtil;

class EmailUtilTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider extractPureEmailAddressProvider
     */
    public function testExtractPureEmailAddress($fullEmailAddress, $pureEmailAddress)
    {
        $this->assertEquals($pureEmailAddress, EmailUtil::extractPureEmailAddress($fullEmailAddress));
    }

    public static function extractPureEmailAddressProvider()
    {
        return array(
            array('john@example.com', 'john@example.com'),
            array('<john@example.com>', 'john@example.com'),
            array('John Smith <john@example.com>', 'john@example.com'),
            array('"John Smith" <john@example.com>', 'john@example.com'),
            array('John Smith on behaf <john@example.com>', 'john@example.com'),
            array('"john@example.com" <john@example.com>', 'john@example.com'),
        );
    }
}
