<?php

namespace Specification\Akeneo\Pim\Permission\Bundle\Api;

use Akeneo\Pim\Permission\Bundle\Api\WarmupGetViewableAttributeCodesQuery;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Permission\GetViewableAttributeCodesForUserInterface;
use Akeneo\Tool\Bundle\ApiBundle\Cache\WarmupQueryCache;
use Akeneo\UserManagement\Component\Model\User;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class WarmupGetViewableAttributeCodesQuerySpec extends ObjectBehavior
{
    function let(
        GetViewableAttributeCodesForUserInterface $getViewableAttributeCodesForUser,
        TokenInterface $token,
        TokenStorageInterface $tokenStorage
    ) {
        $user = new User();
        $user->setId(42);
        $token->getUser()->willReturn($user);
        $tokenStorage->getToken()->willReturn($token);
        $this->beConstructedWith($getViewableAttributeCodesForUser, $tokenStorage);
    }

    function it_is_a_query_cache_warmer()
    {
        $this->shouldImplement(WarmupQueryCache::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(WarmupGetViewableAttributeCodesQuery::class);
    }

    function it_does_nothing_if_no_user_is_authenticated(
        GetViewableAttributeCodesForUserInterface $getViewableAttributeCodesForUser,
        TokenInterface $token
    ) {
        $token->getUser()->willReturn(null);
        $getViewableAttributeCodesForUser->forAttributeCodes(Argument::cetera())->shouldNotBeCalled();

        $this->fromRequest(new Request());
    }

    function it_warms_up_the_query_cache(
        GetViewableAttributeCodesForUserInterface $getViewableAttributeCodesForUser
    ) {
        $content = <<<JSON
{"code": "some_code","values":{"color":[{"data":"red"}],"size":[{"data":"some_other_data"}],"123456":[{"data":"yet_some_other_data"}]}}
{"code":"other_code","values":{"color":[{"data":"blue"}],"name":[{"data":"product_name"}],"description":[{"data":"awesome description"}]}}
JSON;

        $getViewableAttributeCodesForUser->forAttributeCodes(Argument::is(['color', 'size', '123456', 'name', 'description']), 42)
            ->shouldBeCalled();

        $request = new Request([], [], [], [], [], [], $content);
        $this->fromRequest($request);
    }

    function it_ignores_invalid_json(
        GetViewableAttributeCodesForUserInterface $getViewableAttributeCodesForUser
    ) {
        $content = <<<JSON
{"code": "some_code","values":{"color":[{"data":"red"}],"size":[{"data":"some_other_data"}]}}
{"values":{invalid_json}}
{"code": "other_code","values":{"color":[{"data":"blue"}],"size":[{"data":"foo"}]}}
JSON;

        $getViewableAttributeCodesForUser->forAttributeCodes(['color', 'size'], 42)->shouldBeCalled();

        $request = new Request([], [], [], [], [], [], $content);
        $this->fromRequest($request);
    }
}
