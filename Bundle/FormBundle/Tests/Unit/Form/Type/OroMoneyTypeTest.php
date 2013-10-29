<?php

namespace Oro\Bundle\FormBundle\Tests\Unit\Form\Type;

use Oro\Bundle\FormBundle\Form\Type\OroMoneyType;
use Oro\Bundle\LocaleBundle\DependencyInjection\Configuration as LocaleConfiguration;

class OroMoneyTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var OroMoneyType
     */
    protected $formType;

    protected function setUp()
    {
        $localeSettings = $this->getMockBuilder('Oro\Bundle\LocaleBundle\Model\LocaleSettings')
            ->disableOriginalConstructor()
            ->setMethods(array('getCurrency'))
            ->getMock();
        $localeSettings->expects($this->any())
            ->method('getCurrency')
            ->will($this->returnValue(LocaleConfiguration::DEFAULT_CURRENCY));

        $this->formType = new OroMoneyType($localeSettings);
    }

    protected function tearDown()
    {
        unset($this->formType);
    }

    public function testGetName()
    {
        $this->assertEquals(OroMoneyType::NAME, $this->formType->getName());
    }

    public function testGetParent()
    {
        $this->assertEquals('money', $this->formType->getParent());
    }

    public function testSetDefaultOptions()
    {
        $expectedDefaults = array(
            'currency' => LocaleConfiguration::DEFAULT_CURRENCY
        );

        $optionsResolver = $this->getMockBuilder('Symfony\Component\OptionsResolver\OptionsResolverInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $optionsResolver->expects($this->once())
            ->method('setDefaults')
            ->with($expectedDefaults);

        $this->formType->setDefaultOptions($optionsResolver);
    }
}
