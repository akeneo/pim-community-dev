<?php

namespace spec\Pim\Bundle\FilterBundle\Filter\Product;

use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\AssociationType;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Entity\Repository\AssociationTypeRepository;
use Pim\Bundle\CatalogBundle\Model\AbstractAssociation;
use Pim\Bundle\CatalogBundle\Model\AbstractProduct;
use Pim\Bundle\DataGridBundle\Datagrid\RequestParametersExtractorInterface;
use Pim\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Pim\Bundle\FilterBundle\Filter\ProductFilterUtility;
use Prophecy\Argument;
use Symfony\Component\Form\FormFactoryInterface;

class IsAssociatedFilterSpec extends ObjectBehavior
{
    function let(
        FormFactoryInterface $factory,
        ProductFilterUtility $utility,
        RequestParametersExtractorInterface $extractor,
        CustomAssociationTypeRepository $assocRepository,
        ProductManager $manager
    ) {
        $this->beConstructedWith($factory, $utility, $extractor, $assocRepository, $manager);
    }

    function it_is_an_oro_choice_filter()
    {
        $this->shouldBeAnInstanceOf('Oro\Bundle\FilterBundle\Filter\BooleanFilter');
    }

    function it_applies_a_filter_on_product_when_its_in_an_expected_association(
        FilterDatasourceAdapterInterface $datasource,
        $utility,
        QueryBuilder $qb,
        $extractor,
        $assocRepository,
        ProductManager $productManager,
        AssociationType $assocType,
        AbstractAssociation $association,
        AbstractProduct $productOwner,
        AbstractProduct $productAssociatedOne,
        AbstractProduct $productAssociatedTwo,
        $manager
    ) {
        $extractor->getDatagridParameter('_parameters', [])->willReturn([]);
        $extractor->getDatagridParameter('associationType')->willReturn(1);
        $assocRepository->findOneBy(Argument::any())->willReturn($assocType);

        $extractor->getDatagridParameter('product')->willReturn(11);
        $manager->find(11)->willReturn($productOwner);

        $productOwner->getAssociationForType($assocType)->willReturn($association);
        $association->getProducts()->willReturn([$productAssociatedOne, $productAssociatedTwo]);
        $productAssociatedOne->getId()->willReturn(12);
        $productAssociatedTwo->getId()->willReturn(13);

        $utility->applyFilter($datasource, 'id', 'IN', [12, 13])->shouldBeCalled();

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
