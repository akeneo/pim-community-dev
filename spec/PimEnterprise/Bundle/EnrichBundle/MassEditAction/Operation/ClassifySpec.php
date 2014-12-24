<?php

namespace spec\PimEnterprise\Bundle\EnrichBundle\MassEditAction\Operation;

use Oro\Bundle\UserBundle\Entity\User;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\CatalogBundle\Manager\CategoryManager;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

class ClassifySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\EnrichBundle\MassEditAction\Operation\Classify');
    }

    function it_is_a_product_mass_edit_operation()
    {
        $this->shouldHaveType('Pim\Bundle\EnrichBundle\MassEditAction\Operation\ProductMassEditOperation');
    }

    function it_extends_the_base_classify_operation()
    {
        $this->shouldHaveType('Pim\Bundle\EnrichBundle\MassEditAction\Operation\Classify');
    }

    function let(
        CategoryManager $categoryManager,
        SecurityContextInterface $securityContext,
        TokenInterface $token,
        User $user
    ) {
        $securityContext->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);

        $this->beConstructedWith($categoryManager, $securityContext);
    }
}
