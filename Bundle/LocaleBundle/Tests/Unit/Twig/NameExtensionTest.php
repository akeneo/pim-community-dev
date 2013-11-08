<?php

namespace Oro\Bundle\LocaleBundle\Tests\Unit\Twig;

use Oro\Bundle\LocaleBundle\Twig\NameExtension;

class NameExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var NameExtension
     */
    protected $extension;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $formatter;

    protected function setUp()
    {
        $this->formatter = $this->getMockBuilder('Oro\Bundle\LocaleBundle\Formatter\NameFormatter')
            ->disableOriginalConstructor()
            ->getMock();
        $this->extension = new NameExtension($this->formatter);
    }

    public function testGetFilters()
    {
        $filters = $this->extension->getFilters();

        $this->assertCount(1, $filters);

        $this->assertInstanceOf('Twig_SimpleFilter', $filters[0]);
        $this->assertEquals('oro_format_name', $filters[0]->getName());
    }

    public function testFormat()
    {
        $person = $this->getMock('Oro\Bundle\LocaleBundle\Model\FullNameInterface');
        $locale = 'fr_CA';
        $expectedResult = 'John Doe';

        $this->formatter->expects($this->once())->method('format')
            ->with($person, $locale)
            ->will($this->returnValue($expectedResult));

        $this->assertEquals($expectedResult, $this->extension->format($person, $locale));
    }

    public function testGetName()
    {
        $this->assertEquals('oro_locale_name', $this->extension->getName());
    }
}
