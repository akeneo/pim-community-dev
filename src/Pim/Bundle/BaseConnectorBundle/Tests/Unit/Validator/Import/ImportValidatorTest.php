<?php

namespace Pim\Bundle\BaseConnectorBundle\Tests\Unit\Validator\Import;

use Pim\Bundle\ImportExportBundle\Validator\Import\ImportValidator;

/**
 * Tests related class
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ImportValidatorTest extends ImportValidatorTestCase
{
    protected $importValidator;
    protected $entity;
    protected $errors = array(
            'key1' => array(
                array('error1', array('error1_parameters')),
                array('error2', array('error2_parameters')),
            ),
            'key2' => array(
                array('error3', array('error3_parameters')),
            ),
        );

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->importValidator = new ImportValidator($this->validator);
        $this->entity = $this->getMock('Pim\Bundle\CatalogBundle\Model\ReferableInterface');
    }

    /**
     * Test related method
     */
    public function testWithFullValidate()
    {
        $this->entity->expects($this->any())
            ->method('getReference')
            ->will($this->returnValue('id'));
        $this->validator->expects($this->any())
            ->method('validate')
            ->with($this->identicalTo($this->entity))
            ->will($this->returnValue($this->getViolationListMock($this->errors)));
        $errors = $this->importValidator->validate($this->entity, array(), $this->data);
        $this->assertEquals($this->errors, $errors);
    }

    /**
     * Test related method
     *
     * @return null
     */
    public function testWithPropertyValidate()
    {
        $otherErrors = array(
            'key3' => array(
                array('error4' => 'error4_parameters')
            )
        );
        $expectedErrors = $this->errors + $otherErrors;
        unset($expectedErrors['key2']);
        $columns = array('key1_path' => $this->getColumnInfoMock('key1'));
        $this->entity->expects($this->any())
            ->method('getReference')
            ->will($this->returnValue('id'));
        $this->validator->expects($this->any())
            ->method('validateProperty')
            ->will(
                $this->returnCallback(
                    function ($entity, $propertyPath) use ($columns) {
                        $this->assertSame($this->entity, $entity);
                        $label = $columns[$propertyPath]->getLabel();

                        return $this->getViolationListMock(array($propertyPath => $this->errors[$label]));
                    }
                )
            );
        $errors = $this->importValidator->validate($this->entity, $columns, $this->data, $otherErrors);
        $this->assertEquals($expectedErrors, $errors);
    }

    /**
     * @expectedException Pim\Bundle\ImportExportBundle\Exception\DuplicateIdentifierException
     * @expectedExceptionMessage The unique code "id" was already read in this file
     */
    public function testWithDuplicateIdentifiers()
    {
        $this->entity->expects($this->any())
            ->method('getReference')
            ->will($this->returnValue('id'));
        $this->validator->expects($this->once())
            ->method('validate')
            ->with($this->identicalTo($this->entity))
            ->will($this->returnValue($this->getViolationListMock(array())));
        $this->importValidator->validate($this->entity, array(), $this->data);
        $this->importValidator->validate($this->entity, array(), $this->data);
    }

    public function testWithoutIdentifier()
    {
        $this->validator->expects($this->any())
            ->method('validate')
            ->with($this->identicalTo($this->entity))
            ->will($this->returnValue($this->getViolationListMock(array())));
        $this->importValidator->validate($this->entity, array(), $this->data);
        $this->importValidator->validate($this->entity, array(), $this->data);
    }
}
