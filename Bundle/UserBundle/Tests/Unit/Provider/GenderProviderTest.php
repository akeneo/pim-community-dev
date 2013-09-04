<?php

namespace Oro\Bundle\UserBundle\Tests\Provider;

use Oro\Bundle\UserBundle\Provider\GenderProvider;
use Oro\Bundle\UserBundle\Model\Gender;

class GenderProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var GenderProvider
     */
    protected $genderProvider;

    /**
     * @var array
     */
    protected $expectedChoices = array(
        Gender::MALE   => 'oro.user.gender.male.translated',
        Gender::FEMALE => 'oro.user.gender.female.translated',
    );

    protected function setUp()
    {
        $translator = $this->getMockBuilder('Symfony\Component\Translation\TranslatorInterface')
            ->disableOriginalConstructor()
            ->setMethods(array('trans'))
            ->getMockForAbstractClass();
        $translator->expects($this->exactly(2))
            ->method('trans')
            ->will(
                $this->returnCallback(
                    function ($id) {
                        return $id . '.translated';
                    }
                )
            );

        $this->genderProvider = new GenderProvider($translator);
    }

    protected function tearDown()
    {
        unset($this->genderProvider);
    }

    public function testGetChoices()
    {
        // run two times to test internal cache
        $this->assertEquals($this->expectedChoices, $this->genderProvider->getChoices());
        $this->assertEquals($this->expectedChoices, $this->genderProvider->getChoices());
    }

    public function testGetLabelByName()
    {
        $this->assertEquals($this->expectedChoices[Gender::MALE], $this->genderProvider->getLabelByName(Gender::MALE));
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Unknown gender with name "alien"
     */
    public function testGetLabelByNameUnknownGender()
    {
        $this->genderProvider->getLabelByName('alien');
    }
}
