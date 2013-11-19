<?php

namespace Oro\Bundle\FormBundle\Tests\Unit\Extension;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

use Oro\Bundle\FormBundle\Form\Extension\ConstraintAsOptionExtension;
use Oro\Bundle\FormBundle\Validator\ConstraintFactory;

class ConstraintAsOptionExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConstraintAsOptionExtension
     */
    protected $extension;

    protected function setUp()
    {
        $this->extension = new ConstraintAsOptionExtension(new ConstraintFactory());
    }

    protected function tearDown()
    {
        unset($this->extension);
    }

    public function testSetDefaultOptions()
    {
        $constraintOptions = array(
            new NotBlank(),
            array('Length' => array('min' => 3)),
            array('Url' => null),
        );
        $constraintClasses = array(
            'Symfony\Component\Validator\Constraints\NotBlank',
            'Symfony\Component\Validator\Constraints\Length',
            'Symfony\Component\Validator\Constraints\Url'
        );

        $resolver = new OptionsResolver();
        $resolver->setDefaults(array('constraints' => array()));

        $this->extension->setDefaultOptions($resolver);
        $actualOptions = $resolver->resolve(array('constraints' => $constraintOptions));

        $this->assertArrayHasKey('constraints', $actualOptions);
        foreach ($actualOptions['constraints'] as $key => $constraint) {
            $this->assertInstanceOf($constraintClasses[$key], $constraint);
        }
    }

    public function testGetExtendedType()
    {
        $this->assertEquals('form', $this->extension->getExtendedType());
    }
}
