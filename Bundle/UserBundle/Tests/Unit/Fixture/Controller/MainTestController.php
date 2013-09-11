<?php

namespace Oro\Bundle\UserBundle\Tests\Unit\Fixture\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

use Oro\Bundle\SecurityBundle\Annotation\Acl;

/**
 * @Acl(
 *      id = "user_test_main_controller",
 *      name="Test controller",
 *      description = "Test controller for ACL"
 * )
 */
class MainTestController extends Controller
{

    /**
     * @Acl(
     *      id = "user_test_main_controller_action1",
     *      name="action 1",
     *      description = "Action 1",
     *      parent = "user_test_main_controller"
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
     *      name="action 2",
     *      description = "Action 2",
     *      parent = "user_test_main_controller"
     * )
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function test2Action()
    {
        return new Response('test');
    }

    /**
     * @Acl(
     *      id = "user_test_main_controller_action3",
     *      name="action 3",
     *      description = "Action 3",
     *      parent = "user_test_main_controller"
     * )
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
