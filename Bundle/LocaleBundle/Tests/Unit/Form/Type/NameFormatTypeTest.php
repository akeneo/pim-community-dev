<?php

namespace Oro\Bundle\LocaleBundle\Tests\Unit\Form\Type;

use Oro\Bundle\LocaleBundle\Form\Type\NameFormatType;

class NameFormatTypeTest extends \PHPUnit_Framework_TestCase
{
    public function testFormType()
    {
        $provider = $this->getMockBuilder('Oro\Bundle\LocaleBundle\Provider\LocaleSettingsProvider')
            ->disableOriginalConstructor()
            ->getMock();
        $format = '%test%';
        $provider->expects($this->once())
            ->method('getNameFormat')
            ->will($this->returnValue($format));
        $resolver = $this->getMockBuilder('Symfony\Component\OptionsResolver\OptionsResolverInterface')
            ->getMock();
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with(array('data' => $format));

        $type = new NameFormatType($provider);
        $this->assertEquals('text', $type->getParent());
        $this->assertEquals('oro_name_format', $type->getName());
        $type->setDefaultOptions($resolver);
    }
}
