<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Form\Type;

use Symfony\Component\Form\Test\FormIntegrationTestCase;
use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\WorkflowBundle\Form\Type\OroWorkflowStep;
use Oro\Bundle\WorkflowBundle\Model\Step;
use Oro\Bundle\WorkflowBundle\Model\Attribute;
use Oro\Bundle\WorkflowBundle\Model\WorkflowData;

class OroWorkflowStepTest extends FormIntegrationTestCase
{
    /**
     * @var OroWorkflowStep
     */
    protected $type;

    protected function setUp()
    {
        $this->markTestIncomplete('Will be fixed in scope of CRM-313');

        parent::setUp();
        $this->type = new OroWorkflowStep();
    }

    protected function tearDown()
    {
        parent::tearDown();
        unset($this->type);
    }

    /**
     * @dataProvider submitDataProvider
     * @param mixed $submitData
     * @param mixed $formData
     * @param array $formOptions
     * @param array $childrenOptions
     */
    public function testSubmit(
        $submitData,
        $formData,
        array $formOptions,
        array $childrenOptions
    ) {
        $form = $this->factory->create($this->type, null, $formOptions);

        $this->assertSameSize($childrenOptions, $form->all());

        foreach ($childrenOptions as $childName => $childOptions) {
            $this->assertTrue($form->has($childName));
            $childForm = $form->get($childName);
            foreach ($childOptions as $optionName => $optionValue) {
                $this->assertTrue($childForm->getConfig()->hasOption($optionName));
                $this->assertEquals($optionValue, $childForm->getConfig()->getOption($optionName));
            }
        }

        $form->submit($submitData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($formData, $form->getData());
    }

    /**
     * @return array
     */
    public function submitDataProvider()
    {
        // attributes fixture
        $firstAttribute = new Attribute();
        $firstAttribute->setName('first')
            ->setType('string')
            ->setLabel('First')
            ->setOption('form_options', array('required' => true));

        $secondAttribute = new Attribute();
        $secondAttribute->setName('second')
            ->setType('string')
            ->setLabel('Second')
            ->setOption('form_options', array('required' => false));

        $existingDataStep = new Step();
        // $existingDataStep->setAttributes(array($firstAttribute, $secondAttribute));

        // workflow data fixture
        $existingWorkflowData = new WorkflowData();
        $existingWorkflowData->set('first', 'first_string');
        $existingWorkflowData->set('second', 'second_string');

        $customAttributesWorkflowData = new WorkflowData();
        $customAttributesWorkflowData->set('first', 'first_string');

        return array(
            'empty data' => array(
                'submitData'      => array(),
                'formData'        => new WorkflowData(),
                'formOptions'     => array('step' => new Step()),
                'childrenOptions' => array(),
            ),
            'existing data' => array(
                'submitData'      => array('first' => 'first_string', 'second' => 'second_string'),
                'formData'        => $existingWorkflowData,
                'formOptions'     => array('step' => $existingDataStep),
                'childrenOptions' => array(
                    'first'  => array('label' => 'First', 'required' => true),
                    'second' => array('label' => 'Second', 'required' => false),
                ),
            ),
            'custom attributes as array' => array(
                'submitData'      => array('first' => 'first_string'),
                'formData'        => $customAttributesWorkflowData,
                'formOptions'     => array(
                    'step'       => new Step(),
                    'attributes' => array($firstAttribute)),
                'childrenOptions' => array(
                    'first' => array('label' => 'First', 'required' => true),
                ),
            ),
            'custom attributes as collection' => array(
                'submitData'      => array('first' => 'first_string'),
                'formData'        => $customAttributesWorkflowData,
                'formOptions'     => array(
                    'step'       => new Step(),
                    'attributes' => new ArrayCollection(array($firstAttribute)),
                ),
                'childrenOptions' => array(
                    'first' => array('label' => 'First', 'required' => true),
                ),
            ),
        );
    }

    /**
     * @expectedException \Symfony\Component\Form\Exception\UnexpectedTypeException
     * @expectedExceptionMessage Expected argument of type "Oro\Bundle\WorkflowBundle\Model\Step", "array" given
     */
    public function testSubmitIncorrectStepInstance()
    {
        $options = array('step' => array('data'));
        $form = $this->factory->create($this->type, null, $options);
        $form->submit(array());
    }

    /**
     * @expectedException \Symfony\Component\Form\Exception\UnexpectedTypeException
     * @expectedExceptionMessage Expected argument of type "array or Collection", "string" given
     */
    public function testSubmitAttributesNotArrayOrCollection()
    {
        $options = array('step' => new Step(), 'attributes' => 'string_value');
        $form = $this->factory->create($this->type, null, $options);
        $form->submit(array());
    }

    /**
     * @expectedException \Symfony\Component\Form\Exception\UnexpectedTypeException
     * @expectedExceptionMessage Expected argument of type "Oro\Bundle\WorkflowBundle\Model\Attribute", "string" given
     */
    public function testSubmitIncorrectAttributeInstance()
    {
        $options = array('step' => new Step(), 'attributes' => array('string_value'));
        $form = $this->factory->create($this->type, null, $options);
        $form->submit(array());
    }
}
