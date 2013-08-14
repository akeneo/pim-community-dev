<?php
namespace Oro\Bundle\UserBundle\Tests\Unit\Type;

use Symfony\Component\Form\Test\FormIntegrationTestCase;
use Symfony\Component\Form\Extension\Core\View\ChoiceView;

use Oro\Bundle\UserBundle\Form\Type\GenderType;
use Oro\Bundle\UserBundle\Model\Gender;

class GenderTypeTest extends FormIntegrationTestCase
{
    /**
     * @var array
     */
    protected $genderChoices = array(
        Gender::MALE   => 'Male',
        Gender::FEMALE => 'Female',
    );

    /**
     * @var GenderType
     */
    protected $type;

    protected function setUp()
    {
        parent::setUp();

        $genderProvider = $this->getMockBuilder('Oro\Bundle\UserBundle\Provider\GenderProvider')
            ->disableOriginalConstructor()
            ->setMethods(array('getChoices'))
            ->getMock();
        $genderProvider->expects($this->any())
            ->method('getChoices')
            ->will($this->returnValue($this->genderChoices));

        $this->type = new GenderType($genderProvider);
    }

    protected function tearDown()
    {
        parent::tearDown();

        unset($this->type);
    }

    public function testBindValidData()
    {
        $form = $this->factory->create($this->type);

        $form->submit(Gender::MALE);
        $this->assertTrue($form->isSynchronized());
        $this->assertEquals(Gender::MALE, $form->getData());

        $view = $form->createView();
        $this->assertFalse($view->vars['multiple']);
        $this->assertFalse($view->vars['expanded']);
        $this->assertNotEmpty($view->vars['empty_value']);
        $this->assertNotEmpty($view->vars['choices']);

        $actualChoices = array();
        /** @var ChoiceView $choiceView */
        foreach ($view->vars['choices'] as $choiceView) {
            $actualChoices[$choiceView->value] = $choiceView->label;
        }
        $this->assertEquals($this->genderChoices, $actualChoices);
    }

    public function testGetName()
    {
        $this->assertEquals(GenderType::NAME, $this->type->getName());
    }

    public function testGetParent()
    {
        $this->assertEquals('choice', $this->type->getParent());
    }
}
