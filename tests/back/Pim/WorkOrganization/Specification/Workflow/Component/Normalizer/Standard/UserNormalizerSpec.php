<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Component\Normalizer\Standard;

use Akeneo\Pim\WorkOrganization\Workflow\Component\Normalizer\Standard\UserNormalizer;
use Akeneo\UserManagement\Component\Model\UserInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class UserNormalizerSpec extends ObjectBehavior
{
    function let(NormalizerInterface $baseNormalizer)
    {
        $this->beConstructedWith($baseNormalizer, []);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_has_a_cacheable_supports_method()
    {
        $this->shouldImplement(CacheableSupportsMethodInterface::class);
        $this->hasCacheableSupportsMethod()->shouldBe(true);
    }

    function it_is_a_standard_user_normalizer(UserInterface $user)
    {
        $this->shouldHaveType(UserNormalizer::class);
    }

    function it_normalizes_properties_related_to_proposals(NormalizerInterface $baseNormalizer, UserInterface $user)
    {
        $baseNormalizer->normalize($user, 'standard', [])->shouldBeCalled()->willReturn(
            [
                'username' => 'johndoe',
                'foo' => 'bar',
            ]
        );
        $user->getProperty('proposals_state_notifications')->willReturn(null);
        $user->getProperty('proposals_to_review_notification')->willReturn(true);

        $this->normalize($user, 'standard')->shouldReturn(
            [
                'username' => 'johndoe',
                'foo' => 'bar',
                'proposals_state_notifications' => null,
                'proposals_to_review_notification' => true,
            ]
        );
    }
}
