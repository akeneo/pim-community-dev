<?php

namespace spec\PimEnterprise\Bundle\DataGridBundle\Datagrid\Product;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\UserBundle\Entity\User;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\ProductRepository;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use Pim\Bundle\DataGridBundle\Datagrid\Product\ConfigurationRegistry;
use PimEnterprise\Bundle\SecurityBundle\Voter\CategoryVoter;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use Pim\Bundle\CatalogBundle\Model\Product;
use PimEnterprise\Bundle\SecurityBundle\Voter\ProductVoter;

class RowActionsConfiguratorSpec extends ObjectBehavior
{
    function let(
        DatagridConfiguration $datagridConfiguration,
        ConfigurationRegistry $registry,
        SecurityContextInterface $securityContext,
        ProductRepository $productRepository,
        TokenInterface $token,
        User $user,
        CategoryInterface $category
    ) {
        $securityContext->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $this->beConstructedWith($registry, $securityContext, $productRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\DataGridBundle\Datagrid\Product\RowActionsConfigurator');
    }

    function it_configures_the_grid(
        $datagridConfiguration,
        $securityContext,
        ProductRepository $productRepository
    ) {
        $securityContext->isGranted(CategoryVoter::EDIT_PRODUCTS, Argument::any())->willReturn(true);

        $this->configure($datagridConfiguration);
    }

    function it_configures_the_view_actions_for_a_row(ResultRecordInterface $record, ProductRepository $productRepository, Product $product, SecurityContextInterface $securityContext)
    {
        $method = $this->getActionConfigurationClosure();
        $record->getValue('id')->willReturn(42);
        $productRepository->findOneBy(['id' => 42])->willReturn($product);
        $securityContext->isGranted(ProductVoter::PRODUCT_EDIT, $product)->willReturn(false);
        $method($record)->shouldReturn(
            [
                'show' => true,
                'edit' => false,
                'edit_categories' => false,
                'delete' => false,
                'toggle_status' => false
            ]
        );
    }

    function it_configures_the_edit_actions_for_a_row(ResultRecordInterface $record, ProductRepository $productRepository, Product $product, SecurityContextInterface $securityContext)
    {
        $method = $this->getActionConfigurationClosure();
        $record->getValue('id')->willReturn(42);
        $productRepository->findOneBy(['id' => 42])->willReturn($product);
        $securityContext->isGranted(ProductVoter::PRODUCT_EDIT, $product)->willReturn(true);
        $method($record)->shouldReturn(
            [
                'show' => false,
                'edit' => true,
                'edit_categories' => true,
                'delete' => true,
                'toggle_status' => true
            ]
        );
    }
}
