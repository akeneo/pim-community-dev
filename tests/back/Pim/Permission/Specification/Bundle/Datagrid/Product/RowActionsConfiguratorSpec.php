<?php

namespace Specification\Akeneo\Pim\Permission\Bundle\Datagrid\Product;

use Akeneo\Pim\Permission\Bundle\Datagrid\Product\RowActionsConfigurator;
use Akeneo\Pim\Permission\Bundle\Enrichment\Storage\Sql\Product\FetchUserRightsOnProduct;
use Akeneo\Pim\Permission\Bundle\Persistence\Sql\DatagridProductRight\FetchUserRightsOnProductModel;
use Akeneo\Pim\Permission\Bundle\User\UserContext;
use Akeneo\Pim\Permission\Component\Authorization\Model\UserRightsOnProduct;
use Akeneo\Pim\Permission\Component\Authorization\Model\UserRightsOnProductModel;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use PhpSpec\ObjectBehavior;
use Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\Product\ConfigurationRegistry;
use Akeneo\UserManagement\Component\Model\UserInterface;

class RowActionsConfiguratorSpec extends ObjectBehavior
{
    function let(
        ConfigurationRegistry $registry,
        UserInterface $user,
        ResultRecordInterface $productRow,
        ResultRecordInterface $productModelRow,
        FetchUserRightsOnProduct $fetchUserRightsOnProduct,
        FetchUserRightsOnProductModel $fetchUserRightsOnProductModel,
        UserContext $userContext
    ) {
        $productRow->getValue('identifier')->willReturn('product_identifier');
        $productRow->getValue('dataLocale')->willReturn('en_US');
        $productRow->getValue('document_type')->willReturn('product');

        $productModelRow->getValue('identifier')->willReturn('product_model_identifier');
        $productModelRow->getValue('dataLocale')->willReturn('en_US');
        $productModelRow->getValue('document_type')->willReturn('product_model');

        $userContext->getUser()->willReturn($user);
        $user->getId()->willReturn(1);

        $this->beConstructedWith(
            $registry,
            $fetchUserRightsOnProduct,
            $fetchUserRightsOnProductModel,
            $userContext
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(RowActionsConfigurator::class);
    }

    function it_is_possible_for_the_user_to_view_the_product($productRow, $fetchUserRightsOnProduct)
    {
        $fetchUserRightsOnProduct->fetchByIdentifier('product_identifier', 1)->willReturn(
            new UserRightsOnProduct('product_identifier', 1, 0, 0, 1, 1)
        );
        $closure = $this->getActionConfigurationClosure();
        $closure($productRow)->shouldReturn(
            [
                'show' => true,
                'edit' => false,
                'edit_categories' => false,
                'delete' => false,
                'toggle_status' => false,
            ]
        );
    }

    function it_is_possible_for_the_user_to_apply_a_draft_on_product_or_to_enrich_the_product($productRow, $fetchUserRightsOnProduct)
    {
        $fetchUserRightsOnProduct->fetchByIdentifier('product_identifier', 1)->willReturn(
            new UserRightsOnProduct('product_identifier', 1, 1, 0, 1, 1)
        );
        $closure = $this->getActionConfigurationClosure();
        $closure($productRow)->shouldReturn(
            [
                'show' => false,
                'edit' => true,
                'edit_categories' => false,
                'delete' => false,
                'toggle_status' => false,
            ]
        );
    }

    function it_is_possible_for_the_user_to_categorize_the_product($productRow, $fetchUserRightsOnProduct)
    {
        $fetchUserRightsOnProduct->fetchByIdentifier('product_identifier', 1)->willReturn(
            new UserRightsOnProduct('product_identifier', 1, 1, 1, 1, 1)
        );
        $closure = $this->getActionConfigurationClosure();
        $closure($productRow)->shouldReturn(
            [
                'show' => false,
                'edit' => true,
                'edit_categories' => true,
                'delete' => true,
                'toggle_status' => true,
            ]
        );
    }

    function it_is_possible_for_the_user_to_delete_the_product($productRow, $fetchUserRightsOnProduct)
    {
        $fetchUserRightsOnProduct->fetchByIdentifier('product_identifier', 1)->willReturn(
            new UserRightsOnProduct('product_identifier', 1, 1, 1, 1, 1)
        );
        $closure = $this->getActionConfigurationClosure();
        $closure($productRow)->shouldReturn(
            [
                'show' => false,
                'edit' => true,
                'edit_categories' => true,
                'delete' => true,
                'toggle_status' => true,
            ]
        );
    }

    function it_is_possible_for_the_user_to_disable_or_enable_the_product($productRow, $fetchUserRightsOnProduct)
    {
        $fetchUserRightsOnProduct->fetchByIdentifier('product_identifier', 1)->willReturn(
            new UserRightsOnProduct('product_identifier', 1, 1, 1, 1, 1)
        );
        $closure = $this->getActionConfigurationClosure();
        $closure($productRow)->shouldReturn(
            [
                'show' => false,
                'edit' => true,
                'edit_categories' => true,
                'delete' => true,
                'toggle_status' => true,
            ]
        );
    }

    function it_is_possible_for_the_user_to_view_the_product_model($productModelRow, $fetchUserRightsOnProductModel)
    {
        $fetchUserRightsOnProductModel->fetch('product_model_identifier', 1)->willReturn(
            new UserRightsOnProductModel('product_model_identifier', 1, 0, 0, 1)
        );
        $closure = $this->getActionConfigurationClosure();
        $closure($productModelRow)->shouldReturn(
            [
                'show' => true,
                'edit' => false,
                'edit_categories' => false,
                'delete' => false,
                'toggle_status' => false,
            ]
        );
    }

    function it_is_possible_for_the_user_to_apply_a_draft_on_product_model_or_to_enrich_the_product_model($productModelRow, $fetchUserRightsOnProductModel)
    {
        $fetchUserRightsOnProductModel->fetch('product_model_identifier', 1)->willReturn(
            new UserRightsOnProductModel('product_model_identifier', 1, 1, 0, 1)
        );
        $closure = $this->getActionConfigurationClosure();
        $closure($productModelRow)->shouldReturn(
            [
                'show' => false,
                'edit' => true,
                'edit_categories' => false,
                'delete' => false,
                'toggle_status' => false,
            ]
        );
    }

    function it_is_possible_for_the_user_to_categorize_the_product_model($productModelRow, $fetchUserRightsOnProductModel)
    {
        $fetchUserRightsOnProductModel->fetch('product_model_identifier', 1)->willReturn(
            new UserRightsOnProductModel('product_model_identifier', 1, 1, 1, 1)
        );
        $closure = $this->getActionConfigurationClosure();
        $closure($productModelRow)->shouldReturn(
            [
                'show' => false,
                'edit' => true,
                'edit_categories' => true,
                'delete' => true,
                'toggle_status' => true,
            ]
        );
    }

    function it_is_possible_for_the_user_to_delete_the_product_model($productModelRow, $fetchUserRightsOnProductModel)
    {
        $fetchUserRightsOnProductModel->fetch('product_model_identifier', 1)->willReturn(
            new UserRightsOnProductModel('product_model_identifier', 1, 1, 1, 1)
        );
        $closure = $this->getActionConfigurationClosure();
        $closure($productModelRow)->shouldReturn(
            [
                'show' => false,
                'edit' => true,
                'edit_categories' => true,
                'delete' => true,
                'toggle_status' => true,
            ]
        );
    }

    function it_is_possible_for_the_user_to_enable_or_disable_the_product_model($productModelRow, $fetchUserRightsOnProductModel)
    {
        $fetchUserRightsOnProductModel->fetch('product_model_identifier', 1)->willReturn(
            new UserRightsOnProductModel('product_model_identifier', 1, 1, 1, 1)
        );
        $closure = $this->getActionConfigurationClosure();
        $closure($productModelRow)->shouldReturn(
            [
                'show' => false,
                'edit' => true,
                'edit_categories' => true,
                'delete' => true,
                'toggle_status' => true,
            ]
        );
    }
}
