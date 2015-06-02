<?php

namespace spec\PimEnterprise\Bundle\DataGridBundle\Datagrid\Product;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use Oro\Bundle\UserBundle\Entity\User;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\LocaleInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;
use Pim\Bundle\DataGridBundle\Datagrid\Product\ConfigurationRegistry;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

class RowActionsConfiguratorSpec extends ObjectBehavior
{
    function let(
        DatagridConfiguration $datagridConfiguration,
        ConfigurationRegistry $registry,
        SecurityContextInterface $securityContext,
        ProductRepositoryInterface $productRepository,
        LocaleRepositoryInterface $localeRepository,
        TokenInterface $token,
        User $user,
        ResultRecordInterface $record,
        ProductInterface $product,
        LocaleInterface $locale
    ) {
        $securityContext->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);

        $record->getValue('id')->willReturn(42);
        $record->getValue('dataLocale')->willReturn('en_US');
        $localeRepository->findOneBy(['code' => 'en_US'])->willReturn($locale);
        $productRepository->findOneById(42)->willReturn($product);

        $this->beConstructedWith($registry, $securityContext, $productRepository, $localeRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\DataGridBundle\Datagrid\Product\RowActionsConfigurator');
    }

    function it_configures_the_grid($datagridConfiguration, $securityContext)
    {
        $securityContext->isGranted(Attributes::EDIT, Argument::any())->willReturn(true);
        $securityContext->isGranted(Attributes::EDIT_PRODUCTS, Argument::any())->willReturn(true);

        $this->configure($datagridConfiguration);
    }

    function it_configures_the_view_actions_for_a_row($record, $product, $securityContext)
    {
        $securityContext->isGranted(Attributes::EDIT, $product)->willReturn(false);
        $securityContext->isGranted(Attributes::EDIT_PRODUCTS, Argument::any())->willReturn(true);

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

    function it_configures_the_edit_actions_for_a_row($record, $product, $securityContext, $locale)
    {
        $securityContext->isGranted(Attributes::EDIT, $product)->willReturn(true);
        $securityContext->isGranted(Attributes::OWN, $product)->willReturn(true);
        $securityContext->isGranted(Attributes::EDIT_PRODUCTS, $locale)->willReturn(true);

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

    function it_hides_actions_except_the_show_for_a_row_if_user_can_not_edit_the_product(
        $record,
        $product,
        $securityContext,
        $locale
    ) {
        $securityContext->isGranted(Attributes::EDIT, $product)->willReturn(true);
        $securityContext->isGranted(Attributes::OWN, $product)->willReturn(true);
        $securityContext->isGranted(Attributes::EDIT_PRODUCTS, $locale)->willReturn(false);

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

    function it_hides_the_edit_categories_action_if_user_does_not_own_the_product(
        $record,
        $product,
        $securityContext,
        $locale
    ) {
        $securityContext->isGranted(Attributes::EDIT, $product)->willReturn(true);
        $securityContext->isGranted(Attributes::OWN, $product)->willReturn(false);
        $securityContext->isGranted(Attributes::EDIT_PRODUCTS, $locale)->willReturn(true);

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
