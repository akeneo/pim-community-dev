<?php
namespace Oro\Bundle\UserBundle\Tests\Unit\Entity;

use Oro\Bundle\UserBundle\Entity\Acl;
use Oro\Bundle\UserBundle\Entity\Role;
use Oro\Bundle\UserBundle\Annotation\Acl as AnnotationAcl;

class AclTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Oro\Bundle\UserBundle\Entity\Acl
     */
    private $acl;

    public function setUp()
    {
        $this->acl = new Acl();
    }

    public function testToArray()
    {
        $this->acl->setName('test_acl');
        $this->acl->setDescription('test_description');
        $result = $this->acl->toArray();
        $this->assertEquals('test_acl', $result['name']);
        $this->assertEquals('test_description', $result['description']);
    }

    public function testName()
    {
        $this->assertNull($this->acl->getName());
        $this->acl->setName('test_acl');
        $this->assertEquals('test_acl', $this->acl->getName());
    }

    public function testDescription()
    {
        $this->assertNull($this->acl->getDescription());
        $this->acl->setDescription('test_description');
        $this->assertEquals('test_description', $this->acl->getDescription());
    }

    public function testRootNode()
    {
        $this->assertEquals('root', Acl::ROOT_NODE);
    }

    public function testParent()
    {
        $parentAcl = new Acl();
        $this->assertNull($this->acl->getParent());
        $this->acl->setParent($parentAcl);
        $this->assertEquals($parentAcl, $this->acl->getParent());
    }

    public function testData()
    {
        $this->assertNull($this->acl->getId());
        $data = new AnnotationAcl(
            array(
                 'id' => 'test_acl',
                 'name' => 'test name',
                 'description' => 'test description',
                 'parent' => 'test_parent',
            )
        );
        $data->setClass('Oro\Test\Class');
        $data->setMethod('testMethod');
        $this->acl->setData($data);
        $this->acl->setId($data->getId());
        $this->assertEquals('Oro\Test\Class', $this->acl->getClass());
        $this->assertEquals('testMethod', $this->acl->getMethod());
        $this->assertEquals('test name', $this->acl->getName());
        $this->assertEquals('test_acl', $this->acl->getId());
    }

    public function testNestedData()
    {
        $this->acl->setLft(1);
        $this->acl->setRgt(2);
        $this->acl->setRoot(3);
        $this->acl->setLvl(9);
        $this->assertEquals(1, $this->acl->getLft());
        $this->assertEquals(2, $this->acl->getRgt());
        $this->assertEquals(3, $this->acl->getRoot());
        $this->assertEquals(9, $this->acl->getLvl());
    }

    public function testChildren()
    {
        $childAcl = new Acl();
        $this->assertEquals(0, $this->acl->getChildren()->count());
        $this->acl->addChildren($childAcl);
        $this->assertEquals(1, $this->acl->getChildren()->count());
        $this->acl->removeChildren($childAcl);
        $this->assertEquals(0, $this->acl->getChildren()->count());
    }

    public function testRole()
    {
        $role = new Role();
        $this->assertEquals(0, $this->acl->getAccessRoles()->count());
        $this->acl->addAccessRole($role);
        $this->assertEquals(1, $this->acl->getAccessRoles()->count());
        $this->acl->removeAccessRole($role);
        $this->assertEquals(0, $this->acl->getAccessRoles()->count());
    }
}
