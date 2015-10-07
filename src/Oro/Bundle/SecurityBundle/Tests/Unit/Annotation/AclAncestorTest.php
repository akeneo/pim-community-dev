<?php

namespace Oro\Bundle\SecurityBundle\Tests\Unit\Annotation;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

class AclAncestorTest extends \PHPUnit_Framework_TestCase
{
    public function testAncestor()
    {
        $aclAncestor = new AclAncestor(array('value' => 'test_acl'));
        $this->assertEquals('test_acl', $aclAncestor->getId());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testAncestorWithEmptyId()
    {
        $aclAncestor = new AclAncestor(array('value' => ''));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testAncestorWithInvalidId()
    {
        $aclAncestor = new AclAncestor(array('value' => 'test acl'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testAncestorWithMissingId()
    {
        $aclAncestor = new AclAncestor(array());
    }
}
