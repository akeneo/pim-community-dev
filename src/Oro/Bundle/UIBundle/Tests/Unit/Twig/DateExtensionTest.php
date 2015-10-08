<?php

namespace Oro\Bundle\UIBundle\Tests\Unit\Twig;

use BeSimple\SoapCommon\Type\KeyValue\DateTime;
use Oro\Bundle\UIBundle\Twig\DateExtension;

class DateExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $translator;

    /**
     * @var DateExtension
     */
    private $extension;

    /**
     * Set up test environment
     */
    protected function setUp()
    {
        $this->translator = $this->getMockBuilder('Symfony\Component\Translation\TranslatorInterface')
            ->getMock();
        $this->extension = new DateExtension($this->translator);
    }

    public function testGetFilters()
    {
        $filters = $this->extension->getFilters();
        $this->assertInternalType('array', $filters);
        $this->assertArrayHasKey('age', $filters);
        $this->assertInstanceOf('\Twig_Filter_Method', $filters['age']);
        $this->assertArrayHasKey('age_string', $filters);
        $this->assertInstanceOf('\Twig_Filter_Method', $filters['age_string']);
    }

    /**
     * @dataProvider ageDataProvider
     * @param string|\DateTime $date
     * @param array $options
     * @param int $age
     */
    public function testGetAge($date, $options, $age)
    {
        $this->assertEquals($age, $this->extension->getAge($date, $options));
    }

    public function testGetAgeAsStringInvertDiff()
    {
        $date = new \DateTime('+1 year');
        $this->assertEquals('', $this->extension->getAgeAsString($date, array()));
        $this->assertEquals('N/A', $this->extension->getAgeAsString($date, array('default' => 'N/A')));
    }

    public function testGetAgeAsString()
    {
        $date = new \DateTime('-1 year -1 month');
        $this->translator->expects($this->once())
            ->method('transChoice')
            ->with('oro.age', 1, array('%count%' => 1))
            ->will($this->returnValue('age 1'));
        $this->assertEquals('age 1', $this->extension->getAgeAsString($date, array()));
    }

    public function testGetName()
    {
        $this->assertEquals('oro_ui.date', $this->extension->getName());
    }

    public function ageDataProvider()
    {
        $oneYearAgo = new \DateTime('-1 year');
        $oneMonthAgo = new \DateTime('-1 month');
        $oneYearTwoMonthAgo = new \DateTime('-1 year -2 months');
        $tenYearsAgo = new \DateTime('-10 years');
        $inFuture = new \DateTime('+1 year');
        return array(
            array(
                $oneYearAgo->format('Y-m-d'), array(), 1
            ),
            array(
                $oneYearAgo->format('m/d/Y'), array('format' => 'm/d/Y'), 1
            ),
            array(
                $tenYearsAgo->format('m/d/Y'), array('format' => 'm/d/Y', 'timezone' => 'UTC'), 10
            ),
            array(
                $oneMonthAgo, array(), 0
            ),
            array(
                $oneYearAgo, array(), 1
            ),
            array(
                $oneMonthAgo, array(), 0
            ),
            array(
                $oneYearTwoMonthAgo, array(), 1
            ),
            array(
                $tenYearsAgo, array(), 10
            ),
            array(
                $inFuture, array(), null
            ),
            array(
                $inFuture, array('default' => 'N/A'), null
            ),
        );
    }
}
