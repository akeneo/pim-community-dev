<?php

namespace Oro\Bundle\LocaleBundle\Tests\Unit\Form\Type;

use Oro\Bundle\LocaleBundle\Form\Type\TimezoneType;

class TimezoneTypeTest extends \PHPUnit_Framework_TestCase
{
    public function testFormTypeWithoutCache()
    {
        $type = new TimezoneType();
        $resolver = $this->getMockBuilder('Symfony\Component\OptionsResolver\OptionsResolverInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with($this->isType('array'));
        $this->assertEquals('choice', $type->getParent(), 'Unexpected parent');
        $this->assertEquals('oro_locale_timezone', $type->getName(), 'Unexpected form type name');
        $type->setDefaultOptions($resolver);
    }

    /**
     * @depends testFormTypeWithoutCache
     */
    public function testGetTimezonesData()
    {
        $timezones = TimezoneType::getTimezones();
        $this->assertInternalType('array', $timezones);
        $this->assertNotEmpty($timezones);
        $this->assertArrayHasKey('UTC', $timezones);
        $this->assertEquals('(UTC +00:00) Other/UTC', $timezones['UTC']);
    }

    /**
     * @depends testGetTimezonesData
     */
    public function testFormTypeWithFilledCache()
    {
        $timezones = array('Test' => '(UTC +0) Test');

        $cache = $this->getMock('Doctrine\Common\Cache\Cache');
        $cache->expects($this->once())
            ->method('contains')
            ->with('timezones')
            ->will($this->returnValue(true));
        $cache->expects($this->once())
            ->method('fetch')
            ->with('timezones')
            ->will($this->returnValue($timezones));

        $type = new TimezoneType($cache);
        $resolver = $this->getMockBuilder('Symfony\Component\OptionsResolver\OptionsResolverInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with(array('choices' => $timezones));
        $type->setDefaultOptions($resolver);
    }

    /**
     * @depends testGetTimezonesData
     */
    public function testFormTypeWithEmptyCache()
    {
        $cache = $this->getMock('Doctrine\Common\Cache\Cache');
        $cache->expects($this->once())
            ->method('contains')
            ->with('timezones')
            ->will($this->returnValue(false));
        $cache->expects($this->once())
            ->method('save')
            ->with('timezones', $this->isType('array'));

        $type = new TimezoneType($cache);
        $resolver = $this->getMockBuilder('Symfony\Component\OptionsResolver\OptionsResolverInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with($this->isType('array'));
        $type->setDefaultOptions($resolver);
    }

    public function testGetTimezones()
    {
        $timezones = TimezoneType::getTimezonesData();
        $this->assertInternalType('array', $timezones);
        $this->assertNotEmpty($timezones);
        $this->assertArrayHasKey('offset', $timezones[0]);
        $this->assertArrayHasKey('timezone_id', $timezones[0]);
        $this->assertLessThan($timezones[count($timezones) - 1]['offset'], $timezones[0]['offset']);
    }
}
