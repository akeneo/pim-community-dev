<?php

namespace Specification\Akeneo\Pim\Permission\Bundle\Persistence\Cache;

use Akeneo\Pim\Permission\Bundle\Persistence\Cache\LRUCachedGetViewableAttributeCodesForUser;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Permission\GetViewableAttributeCodesForUserInterface;
use PhpSpec\ObjectBehavior;

class LRUCachedGetViewableAttributeCodesForUserSpec extends ObjectBehavior
{
    function let(GetViewableAttributeCodesForUserInterface $getViewableAttributeCodesForUser)
    {
        $grantedAttributeCodes = ['name', 'description', 'image', '123'];
        $requestedAttributeCodes = ['name', 'weight', 'description', 'image', '123', 'size'];
        $getViewableAttributeCodesForUser->forAttributeCodes($requestedAttributeCodes, 42)
            ->willReturn($grantedAttributeCodes);

        $this->beConstructedWith($getViewableAttributeCodesForUser);
        $this->forAttributeCodes($requestedAttributeCodes, 42);
    }

    function it_is_a_get_viewable_attribute_codes_for_user_query()
    {
        $this->shouldImplement(GetViewableAttributeCodesForUserInterface::class);
    }

    function it_is_an_lru_cached_version_of_the_query()
    {
        $this->shouldHaveType(LRUCachedGetViewableAttributeCodesForUser::class);
    }

    function it_gets_granted_attributes_by_doing_a_query_if_the_cache_is_not_hit(
        GetViewableAttributeCodesForUserInterface $getViewableAttributeCodesForUser
    ) {
        $getViewableAttributeCodesForUser->forAttributeCodes(['color', 'brand', 'collection', '456'], 42)
            ->shouldBeCalled()->willReturn(['color', 'collection', '456']);
        $this->forAttributeCodes(['color', 'brand', 'collection', '456'], 42)->shouldReturn(['color', 'collection', '456']);
    }

    function it_gets_granted_attributes_from_the_cache_when_the_cache_is_hit(
        GetViewableAttributeCodesForUserInterface $getViewableAttributeCodesForUser
    ) {
        $getViewableAttributeCodesForUser->forAttributeCodes(['description', 'image', '123', 'size'], 42)->shouldNotBeCalled();
        $this->forAttributeCodes(['description', 'image', '123', 'size'], 42)->shouldBeLike(['description', 'image', '123']);
    }

    function it_mixes_the_call_between_cached_and_non_cached_attribute_codes(
        GetViewableAttributeCodesForUserInterface $getViewableAttributeCodesForUser
    ) {
        $getViewableAttributeCodesForUser->forAttributeCodes(['color', 'brand'], 42)->willReturn(['color']);

        $this->forAttributeCodes(['name', 'weight', 'description', 'image', '123', 'color', 'size', 'brand'], 42)
            ->shouldReturn(['color', 'name', 'description', 'image', '123']);
    }

    function it_can_get_more_than_the_cache_size(
        GetViewableAttributeCodesForUserInterface $getViewableAttributeCodesForUser
    ) {
        $attributeCodes = array_map(
            function (int $i): string {
                return sprintf('attribute_%d', $i);
            },
            range(1, 1200)
        );

        $getViewableAttributeCodesForUser->forAttributeCodes($attributeCodes, 42)->willReturn($attributeCodes);
        $this->forAttributeCodes($attributeCodes, 42)->shouldReturn($attributeCodes);
    }

    function it_clears_the_cache_for_another_user(
        GetViewableAttributeCodesForUserInterface $getViewableAttributeCodesForUser
    ) {
        $getViewableAttributeCodesForUser->forAttributeCodes(['name', 'weight', 'description', 'image', '123', 'size'], 56)
            ->shouldBeCalled()
            ->willReturn(['name', 'weight', 'description', 'image', '123', 'size']);
        $this->forAttributeCodes(['name', 'weight', 'description', 'image', '123', 'size'], 56)
            ->shouldReturn(['name', 'weight', 'description', 'image', '123', 'size']);
    }
}
