<?php

namespace Oro\Bundle\SoapBundle\Tests\Unit\Form\Extension;

use Symfony\Component\HttpFoundation\Request;

use Oro\Bundle\SoapBundle\Form\Extension\DateTimeFormExtension;
use Oro\Bundle\FormBundle\Form\Type\OroDateTimeType;

class DateTimeFormExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function testGetExtendedType()
    {
        $extension = new DateTimeFormExtension(new Request(), '/api');
        $this->assertEquals(OroDateTimeType::NAME, $extension->getExtendedType());
    }
}
