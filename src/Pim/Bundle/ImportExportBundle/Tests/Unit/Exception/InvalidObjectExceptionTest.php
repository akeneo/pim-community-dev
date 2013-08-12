<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Exception;

use Pim\Bundle\ImportExportBundle\Exception\InvalidObjectException;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InvalidObjectExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testGetMessage()
    {
        $form = $this->getFormMock(
            array(
                'name' => $this->getFormErrorMock(
                    'This field should have {{ min }} characters or more.',
                    array(
                        '{{ min }}' => '3'
                    )
                ),
                'age' => $this->getFormErrorMock(
                    'This field should be {{ max }} or less.',
                    array(
                        '{{ max }}' => '21'
                    )
                )
            )
        );

        $exception = new InvalidObjectException($form);

        $this->assertEquals(
            "\n[name] => This field should have 3 characters or more.\n[age] => This field should be 21 or less.",
            $exception->getMessage()
        );
    }

    private function getFormMock($errors)
    {
        $form = $this
            ->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();

        $form->expects($this->any())
            ->method('getErrors')
            ->will($this->returnValue($errors));

        return $form;
    }

    private function getFormErrorMock($template, array $parameters)
    {
        $error = $this
            ->getMockBuilder('Symfony\Component\Form\FormError')
            ->disableOriginalConstructor()
            ->getMock();

        $error->expects($this->any())
            ->method('getMessageTemplate')
            ->will($this->returnValue($template));

        $error->expects($this->any())
            ->method('getMessageParameters')
            ->will($this->returnValue($parameters));

        return $error;
    }
}
