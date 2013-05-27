<?php
namespace Oro\Bundle\UserBundle\Tests\Unit\Annotation;

use Oro\Bundle\UserBundle\Annotation\AclAncestor;

class AclAncestorTest extends \PHPUnit_Framework_TestCase
{
    public function testAncestor()
    {
        $aclAncestor = new AclAncestor(array('value' => 'test_acl'));
        $this->assertEquals('test_acl', $aclAncestor->getId());
        $aclAncestor->setId('enother_acl');
        $this->assertEquals('enother_acl', $aclAncestor->getId());
    }
}
