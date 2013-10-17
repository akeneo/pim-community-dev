<?php

namespace Oro\Bundle\LocaleBundle\Tests\Unit\Form\Type;

use Oro\Bundle\LocaleBundle\Form\Type\NameFormatType;

class NameFormatTypeTest extends \PHPUnit_Framework_TestCase
{
    public function testFormType()
    {
        $localeSettings = $this->getMockBuilder('Oro\Bundle\LocaleBundle\Model\LocaleSettings')
            ->disableOriginalConstructor()
            ->getMock();
        $format = '%test%';
        $localeSettings->expects($this->once())
            ->method('getNameFormat')
            ->will($this->returnValue($format));
        $resolver = $this->getMockBuilder('Symfony\Component\OptionsResolver\OptionsResolverInterface')
            ->getMock();
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with(array('data' => $format));

        $type = new NameFormatType($localeSettings);
        $this->assertEquals('text', $type->getParent());
        $this->assertEquals('oro_name_format', $type->getName());
        $type->setDefaultOptions($resolver);
    }
}
