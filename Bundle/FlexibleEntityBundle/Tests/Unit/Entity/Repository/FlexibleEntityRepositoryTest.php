<?php
namespace Oro\Bundle\FlexibleEntityBundle\Tests\Unit\Entity\Repository;

use Oro\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;

use Oro\Bundle\FlexibleEntityBundle\Entity\Attribute;

use Oro\Bundle\FlexibleEntityBundle\Tests\Unit\AbstractFlexibleManagerTest;
use Oro\Bundle\FlexibleEntityBundle\Entity\Repository\FlexibleEntityRepository;

/**
 * Test related class
 *
 *
 */
class FlexibleEntityRepositoryTest extends AbstractFlexibleManagerTest
{

    /**
     * @var FlexibleEntityRepository
     */
    protected $repository;

    /**
     * Prepare test
     */
    public function setUp()
    {
        parent::setUp();
        // create a mock of repository (mock only getCodeToAttributes method)
        $metadata = $this->entityManager->getClassMetadata($this->flexibleClassName);
        $constructorArgs = array($this->entityManager, $metadata);
        $this->repository = $this->getMock(
            'Oro\Bundle\FlexibleEntityBundle\Entity\Repository\FlexibleEntityRepository',
            array('getCodeToAttributes'),
            $constructorArgs
        );
        $this->repository->setLocale($this->defaultLocale);
        $this->repository->setScope($this->defaultScope);
        // prepare return of getCodeToAttributes calls
        // attribute name
        $attributeName = $this->manager->createAttribute();
        $attributeName->setId(1);
        $attributeName->setCode('name');
        $attributeName->setBackendType(AbstractAttributeType::BACKEND_TYPE_VARCHAR);
        $this->entityManager->persist($attributeName);
        $attributeName->setTranslatable(true);
        // attribute desc
        $attributeDesc = $this->manager->createAttribute();
        $attributeDesc->setId(2);
        $attributeDesc->setCode('description');
        $attributeDesc->setBackendType(AbstractAttributeType::BACKEND_TYPE_TEXT);
        $this->entityManager->persist($attributeDesc);
        $attributeDesc->setTranslatable(true);
        $attributeDesc->setScopable(true);
        // method return
        $return = array($attributeName->getCode() => $attributeName, $attributeDesc->getCode() => $attributeDesc);
        $this->repository->expects($this->any())->method('getCodeToAttributes')->will($this->returnValue($return));
    }

    /**
     * Test related method
     */
    public function testGetLocale()
    {
        $code = 'fr';
        $this->repository->setLocale($code);
        $this->assertEquals($this->repository->getLocale(), $code);
    }

    /**
     * Test related method
     */
    public function testGetScope()
    {
        $code = 'ecommerce';
        $this->repository->setScope($code);
        $this->assertEquals($this->repository->getScope(), $code);
    }

    /**
     * Test related method
     */
    public function testgetFlexibleConfig()
    {
        $this->repository->setFlexibleConfig($this->flexibleConfig);
        $this->assertEquals($this->repository->getFlexibleConfig(), $this->flexibleConfig);
    }

    /**
     * Test related method
     */
    public function testcreateQueryBuilder()
    {
        // with lazy loading
        // TODO : related to grid
        //$qb = $this->repository->createQueryBuilder('MyFlexible');
        //$expectedSql = 'SELECT MyFlexible FROM Oro\Bundle\FlexibleEntityBundle\Tests\Unit\Entity\Demo\Flexible MyFlexible';
        //$this->assertEquals($expectedSql, $qb->getQuery()->getDql());

        // without lazy loading with all values
        $qb = $this->repository->createFlexibleQueryBuilder('MyFlexible');
        $expectedDql = 'SELECT MyFlexible, Value, Attribute, ValueOption, AttributeOptionValue'
            .' FROM Oro\Bundle\FlexibleEntityBundle\Tests\Unit\Entity\Demo\Flexible MyFlexible'
            .' LEFT JOIN MyFlexible.values Value LEFT JOIN Value.attribute Attribute'
            .' LEFT JOIN Value.options ValueOption LEFT JOIN ValueOption.optionValues AttributeOptionValue';
        $this->assertEquals($expectedDql, $qb->getQuery()->getDql());

        // without lazy loading with only values related to attribute codes
        $qb = $this->repository->createFlexibleQueryBuilder('MyFlexible', array('name'));
        $expectedDql = 'SELECT MyFlexible, Value, Attribute, ValueOption, AttributeOptionValue'
            .' FROM Oro\Bundle\FlexibleEntityBundle\Tests\Unit\Entity\Demo\Flexible MyFlexible'
            .' LEFT JOIN MyFlexible.values Value LEFT JOIN Value.attribute Attribute'
            .' LEFT JOIN Value.options ValueOption LEFT JOIN ValueOption.optionValues AttributeOptionValue'
            ." WHERE Attribute.code IN('name') OR Attribute.code IS NULL";
        $this->assertEquals($expectedDql, $qb->getQuery()->getDql());
    }
}
