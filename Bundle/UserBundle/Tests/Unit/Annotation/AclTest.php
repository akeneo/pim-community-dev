<?php
namespace Oro\Bundle\UserBundle\Tests\Unit\Annotation;

use Oro\Bundle\SecurityBundle\Annotation\Acl;

class AclTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Oro\Bundle\SecurityBundle\Annotation\Acl
     */
    private $acl;

    public function setUp()
    {
        $this->acl = new Acl(
            array(
                'id' => 'test_acl',
                'description' => 'test_description',
                'parent' => 'test_parent',
                'name' => 'Test acl Resource'
            )
        );
    }

    public function testName()
    {
        $this->assertEquals('Test acl Resource', $this->acl->getName());
        $this->acl->setName('test');
        $this->assertEquals('test', $this->acl->getName());
    }

    public function testId()
    {
        $this->assertEquals('test_acl', $this->acl->getId());
        $this->acl->setId('test_id');
        $this->assertEquals('test_id', $this->acl->getId());
    }

    public function testDescription()
    {
        $this->assertEquals('test_description', $this->acl->getDescription());
        $this->acl->setDescription('test_descr');
        $this->assertEquals('test_descr', $this->acl->getDescription());
    }

    public function testParent()
    {
        $this->assertEquals('test_parent', $this->acl->getParent());
        $this->acl->setParent('root');
        $this->assertEquals('root', $this->acl->getParent());
        $this->acl->setParent(null);
        $this->assertEquals(false, $this->acl->getParent());
    }
}
