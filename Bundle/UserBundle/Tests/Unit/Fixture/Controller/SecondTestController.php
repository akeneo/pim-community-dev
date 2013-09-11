<?php

namespace Oro\Bundle\UserBundle\Tests\Unit\Fixture\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

use Oro\Bundle\SecurityBundle\Annotation\Acl;

class SecondTestController extends Controller
{

    /**
     * @Acl(
     *      id = "user_test_main_controller_sub_action1",
     *      name="sub action 1",
     *      description = "Sub Action 1",
     *      parent = "user_test_main_controller"
     * )
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function testSub1Action()
    {
        return new Response('test');
    }

    /**
     * @Acl(
     *      id = "user_test_main_controller_sub_action2",
     *      name="sub action 2",
     *      description = "Sub Action 2",
     *      parent = "user_test_main_controller"
     * )
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function testSub2Action()
    {
        return new Response('test');
    }

    /**
     * @Acl(
     *      id = "user_test_main_controller_sub_action3",
     *      name="sub action 3",
     *      description = "Sub Action 3",
     *      parent = "user_test_main_controller"
     * )
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function testSub3Action()
    {
        return new Response('test');
    }
}
