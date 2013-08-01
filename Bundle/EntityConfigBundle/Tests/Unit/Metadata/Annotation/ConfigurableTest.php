<?php

namespace Oro\Bundle\EntityConfigBundle\Tests\Unit\Metadata\Annotation;

use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Configurable;

class ConfigurableTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $annot = new Configurable(array('value' => 'hidden'));
        $this->assertEquals('hidden', $annot->viewMode);

        $annot = new Configurable(array('viewMode' => 'hidden'));
        $this->assertEquals('hidden', $annot->viewMode);

        $this->setExpectedException('\Oro\Bundle\EntityConfigBundle\Exception\AnnotationException');

        $annot = new Configurable(array('viewMode' => 'wrong_value'));
    }
}
