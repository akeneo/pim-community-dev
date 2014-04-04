<?php

namespace spec\Pim\Bundle\FilterBundle\Filter\Product;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Form\FormFactoryInterface;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Pim\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Pim\Bundle\FilterBundle\Filter\ProductFilterUtility;
use Pim\Bundle\CatalogBundle\Model\ProductRepositoryInterface;
use Pim\Bundle\CatalogBundle\Doctrine\ProductQueryBuilderInterface;
use Pim\Bundle\CatalogBundle\Entity\Repository\AssociationTypeRepository;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Entity\AssociationType;
use Pim\Bundle\CatalogBundle\Model\Association;
use Pim\Bundle\CatalogBundle\Model\Product;

class IsAssociatedFilterSpec extends ObjectBehavior
{
    function let(FormFactoryInterface $factory, ProductFilterUtility $utility, RequestParameters $params, CustomAssociationTypeRepository $assocRepository)
    {
        $this->beConstructedWith($factory, $utility, $params, $assocRepository);
    }

    function it_is_an_oro_choice_filter()
    {
        $this->shouldBeAnInstanceOf('Oro\Bundle\FilterBundle\Filter\BooleanFilter');
    }

    function it_applies_a_filter_on_product_when_its_in_an_expected_association(
        FilterDatasourceAdapterInterface $datasource,
        $utility,
        ProductRepositoryInterface $prodRepository,
        ProductQueryBuilderInterface $pqb,
        QueryBuilder $qb,
        RequestParameters $params,
        CustomAssociationTypeRepository $assocRepository,
        ProductManager $productManager,
        AssociationType $assocType,
        Association $association,
        Product $productOwner,
        Product $productAssociatedOne,
        Product $productAssociatedTwo
    ) {
        $params->get('associationType', null)->willReturn(1);
        $assocRepository->findOneBy(Argument::any())->willReturn($assocType);

        $params->get('product', null)->willReturn(11);
        $utility->getProductManager()->willReturn($productManager);
        $productManager->find(11)->willReturn($productOwner);

        $productOwner->getAssociationForType($assocType)->willReturn($association);
        $association->getProducts()->willReturn([$productAssociatedOne, $productAssociatedTwo]);
        $productAssociatedOne->getId()->willReturn(12);
        $productAssociatedTwo->getId()->willReturn(13);

        $datasource->getQueryBuilder()->willReturn($qb);
        $utility->getProductRepository()->willReturn($prodRepository);
        $prodRepository->getProductQueryBuilder($qb)->willReturn($pqb);
        $pqb->addFieldFilter('id', 'IN', [12, 13])->shouldBeCalled();

        $this->apply($datasource, ['type' => null, 'value' => 1]);
    }
}

class CustomAssociationTypeRepository extends AssociationTypeRepository
{
    public function findOneByCode()
    {
        return null;
    }
}
