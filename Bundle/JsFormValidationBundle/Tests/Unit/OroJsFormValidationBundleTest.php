<?php

namespace Oro\Bundle\JsFormValidationBundle\Tests\Unit;

use Oro\Bundle\JsFormValidationBundle\OroJsFormValidationBundle;

class OroJsFormValidationBundleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var OroJsFormValidationBundle
     */
    protected $bundle;

    protected function setUp()
    {
        $this->bundle = new OroJsFormValidationBundle();
    }

    public function testGetParent()
    {
        $this->assertEquals(
            'APYJsFormValidationBundle',
            $this->bundle->getParent()
        );
    }
}
