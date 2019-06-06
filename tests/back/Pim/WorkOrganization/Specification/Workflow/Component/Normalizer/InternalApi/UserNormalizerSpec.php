<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Component\Normalizer\InternalApi;

use Akeneo\Pim\Permission\Bundle\Enrichment\Storage\Sql\Category\GetGrantedCategoryCodes;
use Akeneo\Pim\Permission\Bundle\Entity\Repository\CategoryAccessRepository;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Normalizer\InternalApi\UserNormalizer;
use Akeneo\UserManagement\Component\Model\Group;
use Akeneo\UserManagement\Component\Model\User;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class UserNormalizerSpec extends ObjectBehavior
{
    function let(CategoryAccessRepository $categoryAccessRepository, GetGrantedCategoryCodes $getAllEditableCategoryCodes, GetGrantedCategoryCodes $getAllOwnableCategoryCodes)
    {
        $this->beConstructedWith($categoryAccessRepository, $getAllEditableCategoryCodes, $getAllOwnableCategoryCodes);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(UserNormalizer::class);
    }

    function it_normalizes_a_user(CategoryAccessRepository $categoryAccessRepository, GetGrantedCategoryCodes $getAllEditableCategoryCodes, GetGrantedCategoryCodes $getAllOwnableCategoryCodes)
    {
        $user = new User();

        $categoryAccessRepository->isOwner($user)->willReturn(true);
        $getAllEditableCategoryCodes->forGroupIds(Argument::any())->willReturn(['master']);
        $getAllOwnableCategoryCodes->forGroupIds(Argument::any())->willReturn(['master']);

        $this->normalize($user, null, [])->shouldReturn([
            'display_proposals_to_review_notification' => true,
            'display_proposals_state_notifications' => false,
        ]);

    }
}
