<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Component\Normalizer\InternalApi;

use Akeneo\Pim\Permission\Bundle\Entity\Repository\CategoryAccessRepository;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Normalizer\InternalApi\UserNormalizer;
use Akeneo\UserManagement\Component\Model\User;
use PhpSpec\ObjectBehavior;

class UserNormalizerSpec extends ObjectBehavior
{
    function let(CategoryAccessRepository $categoryAccessRepository)
    {
        $this->beConstructedWith($categoryAccessRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(UserNormalizer::class);
    }

    function it_normalizes_a_user($categoryAccessRepository)
    {
        $user = new User();

        $categoryAccessRepository->isOwner($user)->willReturn(true);

        $categoryAccessRepository->getGrantedCategoryCodes($user, Attributes::EDIT_ITEMS)->willReturn(['master']);
        $categoryAccessRepository->getGrantedCategoryCodes($user, Attributes::OWN_PRODUCTS)->willReturn(['master']);

        $this->normalize($user, null, [])->shouldReturn([
            'display_proposals_to_review_notification' => true,
            'display_proposals_state_notifications' => false,
        ]);

    }
}
