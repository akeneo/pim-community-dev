<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Validator\Import;

/**
 * Test case for import validators
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ImportValidatorTestCase extends \PHPUnit_Framework_TestCase
{
    protected $validator;

    protected $data = array('field' => 'val');

    protected function setUp()
    {
        $this->validator = $this->getMock('Symfony\Component\Validator\ValidatorInterface');
    }

    protected function getColumnInfoMock($label)
    {
        $info = $this->getMock('Pim\Bundle\ImportExportBundle\Transformer\ColumnInfoInterface');
        $info->expects($this->any())
            ->method('getPropertyPath')
            ->will($this->returnValue($label . '_path'));
        $info->expects($this->any())
            ->method('getLabel')
            ->will($this->returnValue($label));

        return $info;
    }

    /**
     * @param array $violationMessages
     *
     * @return \Symfony\Component\Validator\ConstraintViolationList
     */
    public function getViolationListMock(array $violationMessages)
    {
        $list = $this->getMock('Symfony\Component\Validator\ConstraintViolationList');
        $violations = array();

        foreach ($violationMessages as $propertyPath => $pathViolationMessage) {
            foreach ($pathViolationMessage as $config) {
                list($message, $params) = $config;
                $violation = $this->getMock('Symfony\Component\Validator\ConstraintViolationInterface');
                $violation->expects($this->any())
                    ->method('getPropertyPath')
                    ->will($this->returnValue($propertyPath));
                $violation->expects($this->any())
                    ->method('getMessageTemplate')
                    ->will($this->returnValue($message));
                $violation->expects($this->any())
                    ->method('getMessageParameters')
                    ->will($this->returnValue($params));
                $violations[] = $violation;
            }
        }

        $list->expects($this->any())
            ->method('count')
            ->will($this->returnValue(count($violations)));
        $list->expects($this->any())
            ->method('getIterator')
            ->will($this->returnValue(new \ArrayIterator($violations)));

        return $list;
    }
}
