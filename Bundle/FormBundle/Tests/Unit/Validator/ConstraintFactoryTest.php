<?php

namespace Oro\Bundle\FormBundle\Tests\Unit\Validator;

use Oro\Bundle\FormBundle\Validator\ConstraintFactory;

class ConstraintFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $expectedClass
     * @param string $name
     * @param mixed $options
     * @dataProvider createDataProvider
     */
    public function testCreate($expectedClass, $name, $options)
    {
        $factory = new ConstraintFactory();
        $this->assertInstanceOf($expectedClass, $factory->create($name, $options));
    }

    /**
     * @return array
     */
    public function createDataProvider()
    {
        return array(
            'short name' => array(
                'expectedClass' => 'Symfony\Component\Validator\Constraints\NotBlank',
                'name'          => 'NotBlank',
                'options'       => null,
            ),
            'custom class name' => array(
                'expectedClass' => 'Symfony\Component\Validator\Constraints\Length',
                'name'          => 'Symfony\Component\Validator\Constraints\Length',
                'options'       => array('min' => 2, 'max' => 255),
            ),
        );
    }
}
