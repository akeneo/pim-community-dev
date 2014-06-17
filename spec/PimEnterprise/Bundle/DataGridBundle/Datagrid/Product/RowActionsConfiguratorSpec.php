<?php

namespace spec\PimEnterprise\Bundle\DataGridBundle\Datagrid\Product;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\UserBundle\Entity\User;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\Repository\CategoryRepository;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use Pim\Bundle\DataGridBundle\Datagrid\Product\ConfigurationRegistry;
use PimEnterprise\Bundle\SecurityBundle\Voter\CategoryVoter;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

class RowActionsConfiguratorSpec extends ObjectBehavior
{
    function let(
        DatagridConfiguration $datagridConfiguration,
        ConfigurationRegistry $registry,
        SecurityContextInterface $securityContext,
        CategoryRepository $categoryRepository,
        TokenInterface $token,
        User $user,
        CategoryInterface $category
    ) {
        $securityContext->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);

        $categoryRepository->find(Argument::any())->willReturn($category);

        $this->beConstructedWith($registry, $securityContext, $categoryRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\DataGridBundle\Datagrid\Product\RowActionsConfigurator');
    }

    function it_configures_the_grid(
        $datagridConfiguration,
        $securityContext
    ) {
        $securityContext->isGranted(CategoryVoter::EDIT_PRODUCTS, Argument::any())->willReturn(true);

        $this->configure($datagridConfiguration);
    }
}
