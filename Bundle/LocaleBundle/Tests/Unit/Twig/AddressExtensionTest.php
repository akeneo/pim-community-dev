<?php

namespace Oro\Bundle\LocaleBundle\Tests\Unit\Twig;

use Oro\Bundle\LocaleBundle\Twig\AddressExtension;

class AddressExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AddressExtension
     */
    protected $extension;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $formatter;

    protected function setUp()
    {
        $this->formatter = $this->getMockBuilder('Oro\Bundle\LocaleBundle\Formatter\AddressFormatter')
            ->disableOriginalConstructor()
            ->getMock();
        $this->extension = new AddressExtension($this->formatter);
    }

    public function testGetFilters()
    {
        $filters = $this->extension->getFilters();

        $this->assertCount(1, $filters);

        $this->assertInstanceOf('Twig_SimpleFilter', $filters[0]);
        $this->assertEquals('oro_format_address', $filters[0]->getName());
    }

    public function testFormat()
    {
        $address = $this->getMock('Oro\Bundle\LocaleBundle\Model\AddressInterface');
        $country = 'CA';
        $newLineSeparator = '<br/>';
        $expectedResult = 'expected result';

        $this->formatter->expects($this->once())->method('format')
            ->with($address, $country, $newLineSeparator)
            ->will($this->returnValue($expectedResult));

        $this->assertEquals($expectedResult, $this->extension->format($address, $country, $newLineSeparator));
    }

    public function testGetName()
    {
        $this->assertEquals('oro_locale_address', $this->extension->getName());
    }
}
