<?php
namespace Pim\Bundle\FlexibleEntityBundle\Tests\Unit\Entity\Repository;

use Pim\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;

use Pim\Bundle\FlexibleEntityBundle\Entity\Attribute;

use Pim\Bundle\FlexibleEntityBundle\Tests\Unit\AbstractFlexibleManagerTest;
use Pim\Bundle\FlexibleEntityBundle\Entity\Repository\FlexibleEntityRepository;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
    protected function setUp()
    {
        parent::setUp();
        // create a mock of repository (mock only getCodeToAttributes method)
        $metadata = $this->entityManager->getClassMetadata($this->flexibleClassName);
        $constructorArgs = array($this->entityManager, $metadata);
        $this->repository = $this->getMock(
            'Pim\Bundle\FlexibleEntityBundle\Entity\Repository\FlexibleEntityRepository',
            array('getCodeToAttributes'),
            $constructorArgs
        );
        $this->repository->setLocale($this->defaultLocale);
        $this->repository->setScope($this->defaultScope);
        // prepare return of getCodeToAttributes calls
        // attribute name
        $attributeName = new Attribute();
        $attributeName->setId(1);
        $attributeName->setCode('name');
        $attributeName->setBackendType(AbstractAttributeType::BACKEND_TYPE_VARCHAR);
        $this->entityManager->persist($attributeName);
        $attributeName->setTranslatable(true);
        // attribute desc
        $attributeDesc = new Attribute();
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
}
