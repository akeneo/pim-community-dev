<?php

namespace spec\PimEnterprise\Bundle\DataGridBundle\Datagrid\Configuration\Product;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\DataGridBundle\Datagrid\Configuration\Product\ConfigurationRegistry;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
use PimEnterprise\Component\Security\Attributes;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class RowActionsConfiguratorSpec extends ObjectBehavior
{
    function let(
        DatagridConfiguration $datagridConfiguration,
        ConfigurationRegistry $registry,
        AuthorizationCheckerInterface $authorizationChecker,
        ProductRepositoryInterface $productRepository,
        LocaleRepositoryInterface $localeRepository,
        TokenInterface $token,
        UserInterface $user,
        ResultRecordInterface $record,
        ProductInterface $product,
        LocaleInterface $locale,
        TokenStorageInterface $tokenStorage
    ) {
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);

        $record->getValue('identifier')->willReturn('foo');
        $record->getValue('dataLocale')->willReturn('en_US');
        $localeRepository->findOneBy(['code' => 'en_US'])->willReturn($locale);
        $productRepository->findOneByIdentifier('foo')->willReturn($product);

        $this->beConstructedWith($registry, $authorizationChecker, $productRepository, $localeRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\DataGridBundle\Datagrid\Configuration\Product\RowActionsConfigurator');
    }

    function it_configures_the_grid($datagridConfiguration, $authorizationChecker)
    {
        $authorizationChecker->isGranted(Attributes::EDIT, Argument::any())->willReturn(true);
        $authorizationChecker->isGranted(Attributes::EDIT_ITEMS, Argument::any())->willReturn(true);

        $this->configure($datagridConfiguration);
    }

    function it_configures_the_view_actions_for_a_row($record, $product, $authorizationChecker)
    {
        $authorizationChecker->isGranted(Attributes::EDIT, $product)->willReturn(false);
        $authorizationChecker->isGranted(Attributes::EDIT_ITEMS, Argument::any())->willReturn(true);

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

    function it_configures_the_edit_actions_for_a_row($record, $product, $authorizationChecker, $locale)
    {
        $authorizationChecker->isGranted(Attributes::EDIT, $product)->willReturn(true);
        $authorizationChecker->isGranted(Attributes::OWN, $product)->willReturn(true);
        $authorizationChecker->isGranted(Attributes::EDIT_ITEMS, $locale)->willReturn(true);

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
        $authorizationChecker,
        $locale
    ) {
        $authorizationChecker->isGranted(Attributes::EDIT, $product)->willReturn(true);
        $authorizationChecker->isGranted(Attributes::OWN, $product)->willReturn(true);
        $authorizationChecker->isGranted(Attributes::EDIT_ITEMS, $locale)->willReturn(false);

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

    function it_hides_the_edit_categories_and_delete_actions_if_user_does_not_own_the_product(
        $record,
        $product,
        $authorizationChecker,
        $locale
    ) {
        $authorizationChecker->isGranted(Attributes::EDIT, $product)->willReturn(true);
        $authorizationChecker->isGranted(Attributes::OWN, $product)->willReturn(false);
        $authorizationChecker->isGranted(Attributes::EDIT_ITEMS, $locale)->willReturn(true);

        $closure = $this->getActionConfigurationClosure();
        $closure($record)->shouldReturn(
            [
                'show' => false,
                'edit' => true,
                'edit_categories' => false,
                'delete' => false,
                'toggle_status' => false
            ]
        );
    }
}
