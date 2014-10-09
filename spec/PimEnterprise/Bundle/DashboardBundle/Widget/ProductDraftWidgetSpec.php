<?php

namespace spec\PimEnterprise\Bundle\DashboardBundle\Widget;

use Oro\Bundle\UserBundle\Entity\User;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\SecurityBundle\Voter\ProductOwnerVoter;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraft;
use PimEnterprise\Bundle\WorkflowBundle\Repository\ProductDraftOwnershipRepositoryInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

class ProductDraftWidgetSpec extends ObjectBehavior
{
    function let(
        ProductDraftOwnershipRepositoryInterface $ownershipRepo,
        SecurityContextInterface $securityContext,
        TokenInterface $token,
        User $user
    ) {
        $securityContext->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);

        $this->beConstructedWith($securityContext, $ownershipRepo);
    }

    function it_is_a_widget()
    {
        $this->shouldBeAnInstanceOf('Pim\Bundle\DashboardBundle\Widget\WidgetInterface');
    }

    function it_exposes_the_product_draft_widget_template()
    {
        $this->getTemplate()->shouldReturn('PimEnterpriseDashboardBundle:Widget:product_drafts.html.twig');
    }

    function it_exposes_the_product_draft_widget_template_parameters($securityContext)
    {
        $securityContext->isGranted(ProductOwnerVoter::OWN)->willReturn(true);
        $this->getParameters()->shouldBeArray();
    }

    function it_hides_the_widget_if_user_is_not_the_owner_of_any_categories($securityContext, $user)
    {
        $securityContext->isGranted(ProductOwnerVoter::OWN)->willReturn(false);
        $this->getParameters()->shouldReturn(['show' => false]);
        $this->getData()->shouldReturn([]);
    }

    function it_exposes_product_drafts_data(
        $securityContext,
        $user,
        $ownershipRepo,
        ProductDraft $first,
        ProductDraft $second,
        ProductInterface $firstProduct,
        ProductInterface $secondProduct
    ) {
        $securityContext->isGranted(ProductOwnerVoter::OWN)->willReturn(true);
        $ownershipRepo->findApprovableByUser($user, 10)->willReturn([$first, $second]);

        $first->getProduct()->willReturn($firstProduct);
        $second->getProduct()->willReturn($secondProduct);

        $firstProduct->getId()->willReturn(1);
        $secondProduct->getId()->willReturn(2);
        $firstProduct->getLabel()->willReturn('First product');
        $secondProduct->getLabel()->willReturn('Second product');
        $first->getAuthor()->willReturn('Julia');
        $second->getAuthor()->willReturn('Julia');
        $firstCreatedAt = new \DateTime();
        $secondCreatedAt = new \DateTime();
        $first->getCreatedAt()->willReturn($firstCreatedAt);
        $second->getCreatedAt()->willReturn($secondCreatedAt);

        $this->getData()->shouldReturn(
            [
                [
                    'productId'    => 1,
                    'productLabel' => 'First product',
                    'author'       => 'Julia',
                    'createdAt'    => $firstCreatedAt->format('U')
                ],
                [
                    'productId'    => 2,
                    'productLabel' => 'Second product',
                    'author'       => 'Julia',
                    'createdAt'    => $secondCreatedAt->format('U')
                ]
            ]
        );
    }
}
