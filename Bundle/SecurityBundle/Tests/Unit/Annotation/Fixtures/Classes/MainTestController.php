<?php

namespace Oro\Bundle\SecurityBundle\Tests\Unit\Annotation\Fixtures\Classes;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

/**
 * @Acl(
 *      id = "user_test_main_controller",
 *      type="action",
 *      group_name="Test Group",
 *      label = "Test controller for ACL"
 * )
 */
class MainTestController extends Controller
{

    /**
     * @Acl(
     *      id = "user_test_main_controller_action1",
     *      type="entity",
     *      class="AcmeBundle\Entity\SomeClass",
     *      permission="VIEW",
     *      group_name="Test Group",
     *      label="Action 1"
     * )
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function test1Action()
    {
        return new Response('test');
    }

    /**
     * @Acl(
     *      id = "user_test_main_controller_action2",
     *      type="action",
     *      group_name="Another Group",
     *      label="Action 2"
     * )
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function test2Action()
    {
        return new Response('test');
    }

    /**
     * @AclAncestor("user_test_main_controller_action2")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function test3Action()
    {
        return new Response('test');
    }

    public function testNoAclAction()
    {
        return new Response('test');
    }

    public function noActionMethod()
    {
        return array();
    }
}
