<?php

namespace Oro\Bundle\ImportExportBundle\Tests\Unit\Strategy\Import;

use Oro\Bundle\ImportExportBundle\Strategy\Import\ImportStrategyHelper;

class ImportStrategyHelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $managerRegistry;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $validator;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $translator;

    /**
     * @var ImportStrategyHelper
     */
    protected $helper;

    protected function setUp()
    {
        $this->managerRegistry = $this->getMockBuilder('Doctrine\Common\Persistence\ManagerRegistry')
            ->disableOriginalConstructor()
            ->getMock();

        $this->validator = $this->getMockBuilder('Symfony\Component\Validator\ValidatorInterface')
            ->getMock();

        $this->translator = $this->getMockBuilder('Symfony\Component\Translation\TranslatorInterface')
            ->getMock();

        $this->helper = new ImportStrategyHelper($this->managerRegistry, $this->validator, $this->translator);
    }

    /**
     * @expectedException \Oro\Bundle\ImportExportBundle\Exception\InvalidArgumentException
     * @expectedExceptionMessage Basic and imported entities must be instances of the same class
     */
    public function testImportEntityException()
    {
        $basicEntity = new \stdClass();
        $importedEntity  = new \DateTime();
        $excludedProperties = array();

        $this->helper->importEntity($basicEntity, $importedEntity, $excludedProperties);
    }

    /**
     * @expectedException \Oro\Bundle\ImportExportBundle\Exception\LogicException
     * @expectedExceptionMessage Can't find entity manager for stdClass
     */
    public function testImportEntityEntityManagerException()
    {
        $basicEntity = new \stdClass();
        $importedEntity  = new \stdClass();
        $excludedProperties = array();

        $this->managerRegistry->expects($this->once())
            ->method('getManagerForClass')
            ->with(get_class($basicEntity));

        $this->helper->importEntity($basicEntity, $importedEntity, $excludedProperties);
    }

    public function testImportEntity()
    {
        $basicEntity = new \stdClass();
        $importedEntity  = new \stdClass();
        $importedEntity->fieldOne = 'one';
        $importedEntity->fieldTwo = 'two';
        $importedEntity->excludedField = 'excluded';
        $excludedProperties = array('excludedField');

        $metadata = $this->getMockBuilder('\Doctrine\ORM\Mapping\ClassMetadata')
            ->disableOriginalConstructor()
            ->getMock();
        $metadata->expects($this->once())
            ->method('getFieldNames')
            ->will($this->returnValue(array('fieldOne', 'excludedField')));
        $metadata->expects($this->once())
            ->method('getAssociationNames')
            ->will($this->returnValue(array('fieldTwo')));

        $reflectionProperty = $this->getMockBuilder('\ReflectionProperty')
            ->disableOriginalConstructor()
            ->getMock();
        $reflectionProperty->expects($this->atLeastOnce())
            ->method('setAccessible')
            ->with(true);
        $reflectionProperty->expects($this->atLeastOnce())
            ->method('getValue')
            ->with($importedEntity)
            ->will($this->returnValue('testValue'));
        $reflectionProperty->expects($this->atLeastOnce())
            ->method('setValue')
            ->with($basicEntity, 'testValue');
        $metadata->expects($this->exactly(2))
            ->method('getReflectionProperty')
            ->will($this->returnValue($reflectionProperty));

        $entityManager = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $entityManager->expects($this->once())
            ->method('getClassMetadata')
            ->with(get_class($basicEntity))
            ->will($this->returnValue($metadata));

        $this->managerRegistry->expects($this->once())
            ->method('getManagerForClass')
            ->with(get_class($basicEntity))
            ->will($this->returnValue($entityManager));

        $this->helper->importEntity($basicEntity, $importedEntity, $excludedProperties);
    }

    public function testValidateEntityNoErrors()
    {
        $entity = new \stdClass();

        $this->validator->expects($this->once())
            ->method('validate')
            ->with($entity);

        $this->assertNull($this->helper->validateEntity($entity));
    }

    public function testValidateEntity()
    {
        $entity = new \stdClass();

        $violation = $this->getMockBuilder('Symfony\Component\Validator\ConstraintViolationInterface')
            ->getMock();
        $violation->expects($this->once())
            ->method('getPropertyPath')
            ->will($this->returnValue('testPath'));
        $violation->expects($this->once())
            ->method('getMessage')
            ->will($this->returnValue('Error'));
        $violations = array($violation);
        $this->validator->expects($this->once())
            ->method('validate')
            ->with($entity)
            ->will($this->returnValue($violations));

        $this->assertEquals(array('testPath: Error'), $this->helper->validateEntity($entity));
    }

    /**
     * @dataProvider prefixDataProvider
     * @param string|null $prefix
     */
    public function testAddValidationErrors($prefix)
    {
        $validationErrors = array('Error1', 'Error2');
        $expectedPrefix = $prefix;

        $context = $this->getMockBuilder('Oro\Bundle\ImportExportBundle\Context\ContextInterface')
            ->getMock();
        if (null === $prefix) {
            $context->expects($this->once())
                ->method('getReadOffset')
                ->will($this->returnValue(10));
            $this->translator->expects($this->once())
                ->method('trans')
                ->with('oro.importexport.import_error %number%', array('%number%' => 10))
                ->will($this->returnValue('TranslatedError 10'));
            $expectedPrefix = 'TranslatedError 10';
        }

        $context->expects($this->exactly(2))
            ->method('addError')
            ->with($this->stringStartsWith($expectedPrefix . ' Error'));

        $this->helper->addValidationErrors($validationErrors, $context, $prefix);
    }

    public function prefixDataProvider()
    {
        return array(
            array(null),
            array('tst')
        );
    }
}
