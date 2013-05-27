<?php

namespace Oro\Bundle\NavigationBundle\Tests\Unit\Annotation;

use Oro\Bundle\NavigationBundle\Annotation\TitleTemplate;

class TitleTemplateTest extends \PHPUnit_Framework_TestCase
{
    const TEST_VALUE = 'test annotation value';

    /**
     * Test good annotation
     */
    public function testGoodAnnotation()
    {
        $annotation = new TitleTemplate(array('value' => self::TEST_VALUE));

        $this->assertEquals(self::TEST_VALUE, $annotation->getTitleTemplate());
    }

    /**
     * Test bad annotation
     *
     * @expectedException \RuntimeException
     */
    public function testBadAnnotation()
    {
        new TitleTemplate(array());
    }
}
