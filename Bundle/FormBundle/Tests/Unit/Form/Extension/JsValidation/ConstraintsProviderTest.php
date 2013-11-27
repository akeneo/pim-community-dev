<?php

namespace Oro\Bundle\FormBundle\Tests\Unit\Form\Extension\JsValidation;

use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Constraint;

use Symfony\Component\Form\FormConfigBuilder;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Form;

use Oro\Bundle\FormBundle\Form\Extension\JsValidation\ConstraintsProvider;

class ConstraintsProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $metadataFactory;

    /**
     * @var ConstraintsProvider
     */
    protected $constraintsProvider;

    protected function setUp()
    {
        $this->metadataFactory = $this->getMock('Symfony\Component\Validator\MetadataFactoryInterface');
        $this->constraintsProvider = new ConstraintsProvider($this->metadataFactory);
    }

    /**
     * @dataProvider getFormConstraintsDataProvider
     */
    public function testGetFormConstraints(
        FormInterface $formView,
        $expectGetMetadata = false,
        $expectedConstraints = array()
    ) {
        if ($expectGetMetadata) {
            $classMetadata = $this->getMockBuilder('Symfony\Component\Validator\Mapping\ClassMetadata')
                ->disableOriginalConstructor()
                ->getMock();
            $classMetadata->properties = array();
            foreach ($expectGetMetadata['propertyConstraints'] as $property => $constraints) {
                $propertyMetadata = $this->getMockBuilder('Symfony\Component\Validator\Mapping\PropertyMetadata')
                    ->disableOriginalConstructor()
                    ->getMock();
                $propertyMetadata->constraints = $constraints;
                $classMetadata->properties[$property] = $propertyMetadata;
            }
            $this->metadataFactory->expects($this->once())->method('getMetadataFor')
                ->with($expectGetMetadata['value'])
                ->will($this->returnValue($classMetadata));
        }

        $this->assertEquals(
            $expectedConstraints,
            $this->constraintsProvider->getFormConstraints($formView)
        );
    }

    public function getFormConstraintsDataProvider()
    {
        return array(
            'not_mapped' => array(
                'form' => $this->createForm(
                    'email',
                    null,
                    array(
                        'mapped' => false,
                        'constraints' => array($this->createConstraint('NotBlank', array('Default'))),
                    ),
                    $this->createForm('user', 'stdClass', array())
                ),
                'expectGetMetadataFor' => array(),
                'expectedConstraints' => array($this->createConstraint('NotBlank', array('Default')))
            ),
            'doesnt_have_parent' => array(
                'formView' => $this->createForm(
                    'email',
                    null,
                    array(
                        'mapped' => false,
                        'constraints' => array($this->createConstraint('NotBlank', array('Default')))
                    )
                ),
                'expectGetMetadataFor' => array(),
                'expectedConstraints' => array($this->createConstraint('NotBlank', array('Default')))
            ),
            'ignore_all_by_groups' => array(
                'formView' => $this->createForm(
                    'email',
                    null,
                    array(
                        'constraints' => array($this->createConstraint('NotBlank', array('Default')))
                    ),
                    $this->createForm(
                        'user',
                        'stdClass',
                        array(
                            'validation_groups' => array('Custom')
                        )
                    )
                ),
                'expectGetMetadataFor' => array(
                    'value' => 'stdClass',
                    'propertyConstraints' => array(
                        'email' => array($this->createConstraint('Email', array('Default'))),
                    )
                ),
                'expectedConstraints' => array()
            ),
            'ignore_one_by_groups' => array(
                'formView' => $this->createForm(
                    'email',
                    null,
                    array(
                        'constraints' => array($this->createConstraint('NotBlank', array('Default')))
                    ),
                    $this->createForm(
                        'user',
                        'stdClass',
                        array(
                            'validation_groups' => array('Custom')
                        )
                    )
                ),
                'expectGetMetadataFor' => array(
                    'value' => 'stdClass',
                    'propertyConstraints' => array(
                        'email' => array($this->createConstraint('Email', array('Custom'))),
                    )
                ),
                'expectedConstraints' => array($this->createConstraint('Email', array('Custom')))
            ),
            'filter_by_name' => array(
                'formView' => $this->createForm(
                    'email',
                    null,
                    array(
                        'name' => 'email',
                        'constraints' => array($this->createConstraint('NotBlank', array('Default')))
                    ),
                    $this->createForm('user', 'stdClass')
                ),
                'expectGetMetadataFor' => array(
                    'value' => 'stdClass',
                    'propertyConstraints' => array(
                        'email' => array($this->createConstraint('Email', array('Default'))),
                        'username' => array($this->createConstraint('NotBlank', array('Default'))),
                    )
                ),
                'expectedConstraints' => array(
                    $this->createConstraint('Email', array('Default')),
                    $this->createConstraint('NotBlank', array('Default')),
                )
            ),
        );
    }

    /**
     * @param string $name
     * @param string $dataClass
     * @param array $options
     * @param FormInterface $parent
     * @return FormInterface
     */
    protected function createForm($name, $dataClass = null, array $options = array(), FormInterface $parent = null)
    {
        $eventDispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');

        $config = new FormConfigBuilder($name, $dataClass, $eventDispatcher, $options);

        $result = new Form($config);
        $result->setParent($parent);

        return $result;
    }

    /**
     * @param string $name
     * @param array $groups
     * @param array $options
     * @return Constraint
     */
    protected function createConstraint($name, array $groups, array $options = array())
    {
        $className = 'Symfony\\Component\\Validator\\Constraints\\' . $name;

        $result = new $className($options);
        $result->groups = $groups;

        return $result;
    }
}
