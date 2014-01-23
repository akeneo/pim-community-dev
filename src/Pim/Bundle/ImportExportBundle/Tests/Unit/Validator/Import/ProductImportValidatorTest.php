<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Validator\Import;

use Pim\Bundle\ImportExportBundle\Transformer\ORMProductTransformer;
use Pim\Bundle\ImportExportBundle\Validator\Import\ProductImportValidator;

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
        $this->values = [];
        $this->constraintGuesser = $this->getMock(
            'Pim\Bundle\FlexibleEntityBundle\Form\Validator\ConstraintGuesserInterface'
        );
        $this->importValidator = new ProductImportValidator(
            $this->validator,
            $this->constraintGuesser
        );
        $this->product = $this->getMockBuilder('Pim\Bundle\CatalogBundle\Model\Product')
            ->setMethods(['getValue'])
            ->getMock();
        $this->product->expects($this->any())
            ->method('getValue')
            ->will($this->returnCallback([$this, 'getProductValue']));
        $this->identifierColumn = $this->getColumnInfoMock('id');
        $this->identifierColumn->getAttribute()
            ->expects($this->any())
            ->method('getAttributeType')
            ->will($this->returnValue(ORMProductTransformer::IDENTIFIER_ATTRIBUTE_TYPE));
    }

    public function getValidateData()
    {
        return [
            [true],
            [false]
        ];
    }

    /**
     * @dataProvider getValidateData
     */
    public function testValidate($supportsAttribute)
    {
        $this->constraintGuesser->expects($this->any())
            ->method('supportAttribute')
            ->will($this->returnValue($supportsAttribute));
        $columns = [
            $this->identifierColumn,
            $this->getColumnInfoMock('key1'),
            $this->getColumnInfoMock('key2'),
            $this->getColumnInfoMock('key3', false),
            $this->getColumnInfoMock('key4', false),
        ];
        $errors = [
            'key1' => [
                ['error1', ['error1_parameters']],
                ['error2', ['error2_parameters']],
            ],
            'key3' => [
                ['error3', ['error3_parameters']],
            ],
        ];
        $constraintList = [$this->getMock('Symfony\Component\Validator\Constraint')];
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
                            ? [$parts[0] => $errors[$parts[0]]]
                            : [];

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
                            ? [$parts[0] => $errors[$parts[0]]]
                            : [];

                        return $this->getViolationListMock($valueErrors);
                    }
                )
            );
        $validatorErrors = $this->importValidator->validate($this->product, $columns, $this->data);
        $this->assertEquals($validatorErrors, $errors);
    }

    /**
     * @expectedException Pim\Bundle\ImportExportBundle\Exception\DuplicateIdentifierException
     * @expectedExceptionMessage The unique code "id_name_data" was already read in this file
     */
    public function testWithDuplicateIdentifiers()
    {
        $this->constraintGuesser->expects($this->any())
            ->method('supportAttribute')
            ->will($this->returnValue(true));
        $this->validator->expects($this->once())
            ->method('validateValue')
            ->will($this->returnValue($this->getViolationListMock([])));
        $this->importValidator->validate($this->product, [$this->identifierColumn], $this->data);
        $this->importValidator->validate($this->product, [$this->identifierColumn], $this->data);
    }

    /**
     * @param string  $label
     * @param boolean $withAttribute
     *
     * @return ColumnInfoInterface
     */
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
            $attribute = $this->getMock('Pim\Bundle\CatalogBundle\Entity\Attribute');
            $attribute->expects($this->any())
                ->method('getCode')
                ->will($this->returnValue($label . '_code'));
            $column->expects($this->any())
                ->method('getAttribute')
                ->will($this->returnValue($attribute));
        }

        return $column;
    }

    /**
     * @param string $name
     * @param string $locale
     * @param string $scope
     *
     * @return ProductValueInterface
     */
    public function getProductValue($name, $locale, $scope)
    {
        $this->assertEquals('locale', $locale);
        $this->assertEquals('scope', $scope);
        if (!isset($this->values[$name])) {
            $this->values[$name] = $this->getMockBuilder('Pim\Bundle\CatalogBundle\Model\ProductValueInterface')
                ->setMethods(['getData', '__toString'])
                ->getMock();
            $this->values[$name]->expects($this->any())
                ->method('getData')
                ->will($this->returnValue($name . '_data'));
        }

        return $this->values[$name];
    }
}
