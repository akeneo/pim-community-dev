<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Validator\Import;

use Pim\Bundle\ImportExportBundle\Validator\Import\ProductImportValidator;
use Pim\Bundle\ImportExportBundle\Cache\AttributeCache;

/**
 * Tests related class
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductImportValidatorTest extends ImportValidatorTestCase
{
    protected $importValidator;
    protected $identifierColumn;
    protected $constraintGuesser;
    protected $product;
    protected $values;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->values = array();
        $this->constraintGuesser = $this->getMock(
            'Pim\Bundle\FlexibleEntityBundle\Form\Validator\ConstraintGuesserInterface'
        );
        $this->constraintGuesser->expects($this->any())
            ->method('supportAttribute')
            ->will($this->returnValue(true));
        $this->importValidator = new ProductImportValidator(
            $this->validator,
            $this->constraintGuesser
        );
        $this->product = $this->getMockBuilder('Pim\Bundle\CatalogBundle\Model\ProductInterface')
            ->setMethods(array('getValue'))
            ->getMock();
        $this->product->expects($this->any())
            ->method('getValue')
            ->will($this->returnCallback(array($this, 'getProductValue')));
        $this->identifierColumn = $this->getColumnInfoMock('id');
        $this->identifierColumn->getAttribute()
            ->expects($this->any())
            ->method('getAttributeType')
            ->will($this->returnValue(AttributeCache::IDENTIFIER_ATTRIBUTE_TYPE));
    }

    public function testValidate()
    {
        $columns = array(
            $this->identifierColumn,
            $this->getColumnInfoMock('key1'),
            $this->getColumnInfoMock('key2'),
            $this->getColumnInfoMock('key3', false),
            $this->getColumnInfoMock('key4', false),
        );
        $errors = array(
            'key1' => array(
                array('error1', array('error1_parameters')),
                array('error2', array('error2_parameters')),
            ),
            'key3' => array(
                array('error3', array('error3_parameters')),
            ),
        );
        $constraintList = array($this->getMock('Symfony\Component\Validator\Constraint'));
        $this->constraintGuesser->expects($this->any())
            ->method('guessConstraints')
            ->will($this->returnValue($constraintList));
        $this->validator->expects($this->any())
            ->method('validateValue')
            ->will(
                $this->returnCallback(
                    function ($value) use ($constraintList, $errors) {
                        $this->assertSame($constraintList[0], $constraintList[0]);
                        $parts = explode('_', $value);
                        $valueErrors = isset($errors[$parts[0]])
                            ? array($parts[0] => $errors[$parts[0]])
                            : array();

                        return $this->getViolationListMock($valueErrors);
                    }
                )
            );
        $this->validator->expects($this->any())
            ->method('validateProperty')
            ->will(
                $this->returnCallback(
                    function ($product, $propertyPath) use ($errors) {
                        $this->assertSame($this->product, $product);
                        $parts = explode('_', $propertyPath);
                        $valueErrors = isset($errors[$parts[0]])
                            ? array($parts[0] => $errors[$parts[0]])
                            : array();

                        return $this->getViolationListMock($valueErrors);
                    }
                )
            );
        $validatorErrors = $this->importValidator->validate($this->product, $columns, $this->data);
        $this->assertEquals($validatorErrors, $errors);
    }

    /**
     * @expectedException Pim\Bundle\ImportExportBundle\Exception\DuplicateIdentifierException
     * @expectedExceptionMessage The "id" attribute is unique, the value "id_name_data" was already read in this file
     */
    public function testWithDuplicateIdentifiers()
    {
        $this->validator->expects($this->once())
            ->method('validateValue')
            ->will($this->returnValue($this->getViolationListMock(array())));
        $this->importValidator->validate($this->product, array($this->identifierColumn), $this->data);
        $this->importValidator->validate($this->product, array($this->identifierColumn), $this->data);
    }

    protected function getColumnInfoMock($label, $withAttribute = true)
    {
        $column = parent::getColumnInfoMock($label);
        $column->expects($this->any())
            ->method('getName')
            ->will($this->returnValue($label . '_name'));
        $column->expects($this->any())
            ->method('getLocale')
            ->will($this->returnValue('locale'));
        $column->expects($this->any())
            ->method('getScope')
            ->will($this->returnValue('scope'));
        if ($withAttribute) {
            $attribute = $this->getMock('Pim\Bundle\CatalogBundle\Entity\ProductAttribute');
            $attribute->expects($this->any())
                ->method('getCode')
                ->will($this->returnValue($label . '_code'));
            $column->expects($this->any())
                ->method('getAttribute')
                ->will($this->returnValue($attribute));
        }

        return $column;
    }
    public function getProductValue($name, $locale, $scope)
    {
        $this->assertEquals('locale', $locale);
        $this->assertEquals('scope', $scope);
        if (!isset($this->values[$name])) {
            $this->values[$name] = $this->getMockBuilder('Pim\Bundle\CatalogBundle\Model\ProductValueInterface')
                ->setMethods(array('getData', '__toString'))
                ->getMock();
            $this->values[$name]->expects($this->any())
                ->method('getData')
                ->will($this->returnValue($name . '_data'));
        }

        return $this->values[$name];
    }
}
