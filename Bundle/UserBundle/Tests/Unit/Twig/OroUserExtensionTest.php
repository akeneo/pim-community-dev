<?php

namespace Oro\Bundle\UserBundle\Tests\Twig;

use Oro\Bundle\UserBundle\Twig\OroUserExtension;
use Oro\Bundle\UserBundle\Model\Gender;

class OroUserExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var OroUserExtension
     */
    protected $twigExtension;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $genderProvider;

    protected function setUp()
    {
        $this->genderProvider = $this->getMock(
            'Oro\Bundle\UserBundle\Provider\GenderProvider',
            array('getLabelByName'),
            array(),
            '',
            false
        );

        $this->twigExtension = new OroUserExtension($this->genderProvider);
    }

    protected function tearDown()
    {
        unset($this->genderProvider);
        unset($this->twigExtension);
    }

    public function testGetName()
    {
        $this->assertEquals('user_extension', $this->twigExtension->getName());
    }

    public function testGetFunctions()
    {
        $expectedFunctions = array(
            'oro_gender'       => 'getGenderLabel',
        );

        $actualFunctions = $this->twigExtension->getFunctions();
        $this->assertSameSize($expectedFunctions, $actualFunctions);

        foreach ($expectedFunctions as $twigFunction => $internalMethod) {
            $this->assertArrayHasKey($twigFunction, $actualFunctions);
            $this->assertInstanceOf('\Twig_Function_Method', $actualFunctions[$twigFunction]);
            $this->assertAttributeEquals($internalMethod, 'method', $actualFunctions[$twigFunction]);
        }
    }

    public function testGetGenderLabel()
    {
        $label = 'Male';
        $this->genderProvider->expects($this->once())
            ->method('getLabelByName')
            ->with(Gender::MALE)
            ->will($this->returnValue($label));

        $this->assertNull($this->twigExtension->getGenderLabel(null));
        $this->assertEquals($label, $this->twigExtension->getGenderLabel(Gender::MALE));
    }
}
