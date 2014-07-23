<?php

namespace spec\PimEnterprise\Bundle\DataGridBundle\Datagrid\Product;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\UserBundle\Entity\User;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\ProductRepository;
use Pim\Bundle\DataGridBundle\Datagrid\Product\ConfigurationRegistry;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use Pim\Bundle\CatalogBundle\Model\Product;
use PimEnterprise\Bundle\SecurityBundle\Attributes;

class RowActionsConfiguratorSpec extends ObjectBehavior
{
    function let(
        DatagridConfiguration $datagridConfiguration,
        ConfigurationRegistry $registry,
        SecurityContextInterface $securityContext,
        ProductRepository $productRepository,
        TokenInterface $token,
        User $user,
        ResultRecordInterface $record,
        Product $product
    ) {
        $securityContext->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);

        $record->getValue('id')->willReturn(42);
        $productRepository->findOneBy(['id' => 42])->willReturn($product);

        $this->beConstructedWith($registry, $securityContext, $productRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\DataGridBundle\Datagrid\Product\RowActionsConfigurator');
    }

    function it_configures_the_grid($datagridConfiguration, $securityContext)
    {
        $securityContext->isGranted(Attributes::EDIT_PRODUCTS, Argument::any())->willReturn(true);

        $this->configure($datagridConfiguration);
    }

    function it_configures_the_view_actions_for_a_row($record, $product, $securityContext)
    {
        $securityContext->isGranted(Attributes::EDIT_PRODUCT, $product)->willReturn(false);

        $closure = $this->getActionConfigurationClosure();
        $closure($record)->shouldReturn(
            [
                'show' => true,
                'edit' => false,
                'edit_categories' => false,
                'delete' => false,
                'toggle_status' => false
            ]
        );
    }

    function it_configures_the_edit_actions_for_a_row($record, $product, $securityContext)
    {
        $securityContext->isGranted(Attributes::EDIT_PRODUCT, $product)->willReturn(true);
        $securityContext->isGranted(Attributes::OWNER, $product)->willReturn(true);

        $closure = $this->getActionConfigurationClosure();
        $closure($record)->shouldReturn(
            [
                'show' => false,
                'edit' => true,
                'edit_categories' => true,
                'delete' => true,
                'toggle_status' => true
            ]
        );
    }

    function it_hides_the_edit_categories_action_if_user_does_not_own_the_product($record, $product, $securityContext)
    {
        $securityContext->isGranted(Attributes::EDIT_PRODUCT, $product)->willReturn(true);
        $securityContext->isGranted(Attributes::OWNER, $product)->willReturn(false);

        $closure = $this->getActionConfigurationClosure();
        $closure($record)->shouldReturn(
            [
                'show' => false,
                'edit' => true,
                'edit_categories' => false,
                'delete' => true,
                'toggle_status' => true
            ]
        );
    }
}
