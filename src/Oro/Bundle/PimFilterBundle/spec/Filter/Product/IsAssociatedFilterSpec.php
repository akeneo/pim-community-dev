<?php

namespace spec\Oro\Bundle\PimFilterBundle\Filter\Product;

use Oro\Bundle\FilterBundle\Filter\BooleanFilter;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository\AssociationTypeRepository;
use Oro\Bundle\PimDataGridBundle\Datagrid\Request\RequestParametersExtractorInterface;
use Oro\Bundle\PimFilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\PimFilterBundle\Filter\ProductFilterUtility;
use Akeneo\Pim\Enrichment\Component\Product\Model\AbstractAssociation;
use Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Prophecy\Argument;
use Symfony\Component\Form\FormFactoryInterface;

class IsAssociatedFilterSpec extends ObjectBehavior
{
    function let(
        FormFactoryInterface $factory,
        ProductFilterUtility $utility,
        RequestParametersExtractorInterface $extractor,
        CustomAssociationTypeRepository $assocRepository,
        ProductRepositoryInterface $productRepository
    ) {
        $this->beConstructedWith($factory, $utility, $extractor, $assocRepository, $productRepository);
    }

    function it_is_an_oro_choice_filter()
    {
        $this->shouldBeAnInstanceOf(BooleanFilter::class);
    }

    function it_applies_a_filter_on_product_when_its_in_an_expected_association(
        FilterDatasourceAdapterInterface $datasource,
        $utility,
        $extractor,
        $assocRepository,
        AssociationTypeInterface $assocType,
        AbstractAssociation $association,
        ProductInterface $productOwner,
        ProductInterface $productAssociatedOne,
        ProductInterface $productAssociatedTwo,
        $productRepository
    ) {
        $extractor->getDatagridParameter('_parameters', [])->willReturn([]);
        $extractor->getDatagridParameter('associationType')->willReturn(1);
        $assocRepository->findOneBy(Argument::any())->willReturn($assocType);

        $extractor->getDatagridParameter('product')->willReturn(11);
        $productRepository->find(11)->willReturn($productOwner);

        $productOwner->getAssociationForType($assocType)->willReturn($association);
        $association->getProducts()->willReturn([$productAssociatedOne, $productAssociatedTwo]);
        $productAssociatedOne->getId()->willReturn(12);
        $productAssociatedTwo->getId()->willReturn(13);

        $utility->applyFilter($datasource, 'id', 'IN', ['12', '13'])->shouldBeCalled();

        $this->apply($datasource, ['type' => null, 'value' => 1]);
    }
}

class CustomAssociationTypeRepository extends AssociationTypeRepository
{
    function findOneByCode()
    {
        return null;
    }
}
