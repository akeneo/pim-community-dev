<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Twig;

use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use PimEnterprise\Bundle\UserBundle\Entity\UserInterface;
use PimEnterprise\Component\Security\Attributes;

class VisibleUserPreferencesExtensionSpec extends ObjectBehavior
{
    function let(CategoryAccessRepository $catAccessRepository)
    {
        $this->beConstructedWith($catAccessRepository);
    }

    function it_is_a_twig_extension()
    {
        $this->shouldImplement('\Twig_Extension');
    }

    function it_has_functions()
    {
        $functions = $this->getFunctions();

        $functions->shouldHaveCount(2);
        $functions[0]->getName()->shouldBeEqualTo('is_proposal_to_review_field_visible');
        $functions[0]->shouldBeAnInstanceOf(\Twig_SimpleFunction::class);
        $functions[1]->getName()->shouldBeEqualTo('is_proposal_state_field_visible');
        $functions[1]->shouldBeAnInstanceOf(\Twig_SimpleFunction::class);
    }

    function it_checks_if_the_field_notification_for_proposals_to_review_has_to_be_show(
        $catAccessRepository,
        UserInterface $user1,
        UserInterface $user2
    ) {
        $catAccessRepository->isOwner($user1)->willReturn(true);
        $catAccessRepository->isOwner($user2)->willReturn(false);

        $this->isProposalToReviewFieldVisible($user1)->shouldReturn(true);
        $this->isProposalToReviewFieldVisible($user2)->shouldReturn(false);
    }

    function it_checks_if_the_field_notification_to_know_state_of_a_proposal_has_to_be_show(
        $catAccessRepository,
        UserInterface $user1,
        UserInterface $user2
    ) {
        $catAccessRepository
            ->getGrantedCategoryCodes($user1, Attributes::EDIT_ITEMS)
            ->willReturn(['high_tech', 'tv']);
        $catAccessRepository
            ->getGrantedCategoryCodes($user1, Attributes::OWN_PRODUCTS)
            ->willReturn(['high_tech']);

        $catAccessRepository
            ->getGrantedCategoryCodes($user2, Attributes::EDIT_ITEMS)
            ->willReturn(['high_tech', 'tv']);
        $catAccessRepository
            ->getGrantedCategoryCodes($user2, Attributes::OWN_PRODUCTS)
            ->willReturn(['high_tech', 'tv']);

        $this->isProposalStateFieldVisible($user1)->shouldReturn(true);
        $this->isProposalStateFieldVisible($user2)->shouldReturn(false);
    }
}
