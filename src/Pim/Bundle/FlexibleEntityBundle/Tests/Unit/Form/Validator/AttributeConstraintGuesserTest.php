<?php

namespace Pim\Bundle\FlexibleEntityBundle\Tests\Unit\Form\Validator;

use Pim\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;
use Symfony\Component\Validator\Constraints;
use Pim\Bundle\FlexibleEntityBundle\Form\Validator\AttributeConstraintGuesser;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChainedConstraintGuesserTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->target = new AttributeConstraintGuesser();
    }

    public function testInstanceOfContraintGuesserInterface()
    {
        $this->assertInstanceOf(
            'Pim\Bundle\FlexibleEntityBundle\Form\Validator\ConstraintGuesserInterface',
            $this->target
        );
    }

    public function testGuessNotBlankConstraints()
    {
        $this->assertContainsInstanceOf(
            'Symfony\Component\Validator\Constraints\NotBlank',
            $this->target->guessConstraints(
                $this->getAttributeMock(array('required' => true))
            )
        );
    }

    public function testGuessDateConstraints()
    {
        $this->assertContainsInstanceOf(
            'Symfony\Component\Validator\Constraints\Date',
            $this->target->guessConstraints(
                $this->getAttributeMock(array('backendType' => AbstractAttributeType::BACKEND_TYPE_DATE))
            )
        );
    }

    public function testGuessDateTimeConstraints()
    {
        $this->assertContainsInstanceOf(
            'Symfony\Component\Validator\Constraints\DateTime',
            $this->target->guessConstraints(
                $this->getAttributeMock(array('backendType' => AbstractAttributeType::BACKEND_TYPE_DATETIME))
            )
        );
    }

    private function getAttributeMock(array $options)
    {
        $options = array_merge(
            array(
                'required'    => false,
                'backendType' => null,
            ),
            $options
        );

        $attribute = $this->getMock('Pim\Bundle\FlexibleEntityBundle\Model\AbstractAttribute');

        $attribute->expects($this->any())
            ->method('getBackendType')
            ->will($this->returnValue($options['backendType']));

        $attribute->expects($this->any())
            ->method('isRequired')
            ->will($this->returnValue($options['required']));

        return $attribute;
    }

    private function assertContainsInstanceOf($class, $constraints)
    {
        foreach ($constraints as $constraint) {
            if ($constraint instanceof $class) {
                return true;
            }
        }

        throw new \Exception(sprintf('Expecting constraints to contain instance of "%s"', $class));
    }
}
