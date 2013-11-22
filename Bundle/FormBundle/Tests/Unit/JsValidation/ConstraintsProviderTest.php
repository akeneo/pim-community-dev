<?php

namespace Oro\Bundle\FormBundle\Tests\Unit\JsValidation\Event;

use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Constraint;

use Symfony\Component\Form\FormView;

use Oro\Bundle\FormBundle\JsValidation\ConstraintsProvider;

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
     * @dataProvider getFormViewConstraintsDataProvider
     */
    public function testGetFormViewConstraints(
        FormView $formView,
        array $validationGroups,
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

        $this->assertInstanceOf(
            'Doctrine\Common\Collections\Collection',
            $this->constraintsProvider->getFormViewConstraints($formView, $validationGroups)
        );

        $this->assertEquals(
            $expectedConstraints,
            $this->constraintsProvider->getFormViewConstraints($formView, $validationGroups)->toArray()
        );
    }

    public function getFormViewConstraintsDataProvider()
    {
        return array(
            'not_mapped' => array(
                'formView' => $this->createFormView(
                    array(
                        'mapped' => false,
                        'name' => 'email',
                        'constraints' => array($this->createConstraint('NotBlank', array('Default')))
                    ),
                    $this->createFormView(array('full_name' => 'user', 'data_class' => 'TestUserClass'))
                ),
                'validationGroups' => array('Default'),
                'expectGetMetadataFor' => array(),
                'expectedConstraints' => array($this->createConstraint('NotBlank', array('Default')))
            ),
            'doesnt_have_parent' => array(
                'formView' => $this->createFormView(
                    array(
                        'mapped' => false,
                        'name' => 'email',
                        'constraints' => array($this->createConstraint('NotBlank', array('Default')))
                    )
                ),
                'validationGroups' => array('Default'),
                'expectGetMetadataFor' => array(),
                'expectedConstraints' => array($this->createConstraint('NotBlank', array('Default')))
            ),
            'ignore_all_by_groups' => array(
                'formView' => $this->createFormView(
                    array(
                        'name' => 'email',
                        'constraints' => array($this->createConstraint('NotBlank', array('Test')))
                    ),
                    $this->createFormView(array('full_name' => 'user', 'data_class' => 'TestUserClass'))
                ),
                'validationGroups' => array('Default'),
                'expectGetMetadataFor' => array(
                    'value' => 'TestUserClass',
                    'propertyConstraints' => array(
                        'email' => array($this->createConstraint('Email', array('Test'))),
                    )
                ),
                'expectedConstraints' => array()
            ),
            'ignore_one_by_groups' => array(
                'formView' => $this->createFormView(
                    array(
                        'name' => 'email',
                        'constraints' => array($this->createConstraint('NotBlank', array('Test')))
                    ),
                    $this->createFormView(array('full_name' => 'user', 'data_class' => 'TestUserClass'))
                ),
                'validationGroups' => array('Default'),
                'expectGetMetadataFor' => array(
                    'value' => 'TestUserClass',
                    'propertyConstraints' => array(
                        'email' => array($this->createConstraint('Email', array('Default'))),
                    )
                ),
                'expectedConstraints' => array($this->createConstraint('Email', array('Default')))
            ),
            'filter_by_name' => array(
                'formView' => $this->createFormView(
                    array(
                        'name' => 'email',
                        'constraints' => array($this->createConstraint('NotBlank', array('Default')))
                    ),
                    $this->createFormView(array('full_name' => 'user', 'data_class' => 'TestUserClass'))
                ),
                'validationGroups' => array('Default'),
                'expectGetMetadataFor' => array(
                    'value' => 'TestUserClass',
                    'propertyConstraints' => array(
                        'email' => array($this->createConstraint('Email', array('Default'))),
                        'username' => array($this->createConstraint('NotBlank', array('Default'))),
                    )
                ),
                'expectedConstraints' => array(
                    $this->createConstraint('NotBlank', array('Default')),
                    $this->createConstraint('Email', array('Default'))
                )
            ),
        );
    }

    /**
     * @dataProvider getConstraintNameDataProvider
     * @param string $constraint
     * @param string $expectedName
     */
    public function testGetConstraintName($constraint, $expectedName)
    {
        $this->assertEquals($expectedName, $this->constraintsProvider->getConstraintName($constraint));
    }

    /**
     * @return array
     */
    public function getConstraintNameDataProvider()
    {
        $mockConstraint = $this->getMock('Symfony\Component\Validator\Constraint');
        return array(
            array(
                new Constraints\NotBlank(),
                'NotBlank'
            ),
            array(
                $mockConstraint,
                get_class($mockConstraint)
            )
        );
    }

    /**
     * @dataProvider getConstraintPropertiesDataProvider
     */
    public function testGetConstraintProperties($constraint, $expected)
    {
        $this->assertEquals($expected, $this->constraintsProvider->getConstraintProperties($constraint));
    }

    /**
     * @return array
     */
    public function getConstraintPropertiesDataProvider()
    {
        $constraint = new Constraints\NotNull();
        $constraint->message = array(
            'object' => new \stdClass(),
            'array' => array(
                'object' => new \stdClass(),
                'integer' => 2,
            ),
            'integer' => 1,
        );

        return array(
            array(
                $this->createConstraint('NotBlank', array('Default')),
                array(
                    'message' => 'This value should not be blank.'
                )
            ),
            array(
                $constraint,
                array(
                    'message' => array(
                        'array' => array(
                            'integer' => 2
                        ),
                        'integer' => 1
                    )
                )
            )
        );
    }

    /**
     * @param array array
     * @param FormView $parent
     * @return FormView
     */
    protected function createFormView(array $vars = array(), FormView $parent = null)
    {
        $result = new FormView();
        $result->vars = $vars;
        $result->parent = $parent;
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
