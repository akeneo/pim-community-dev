<?php

namespace spec\Pim\Bundle\EnrichBundle\MassEditAction;

use PhpSpec\ObjectBehavior;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\AbstractQuery;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\CatalogBundle\Model\ProductRepositoryInterface;

class ChangeFamilySpec extends ObjectBehavior
{
    function let(ProductRepositoryInterface $productRepository)
    {
        $productRepository->implement('Doctrine\Common\Persistence\ObjectRepository');
        $this->beConstructedWith($productRepository);
    }

    function it_is_a_mass_edit_action()
    {
        $this->shouldBeAnInstanceOf('Pim\Bundle\EnrichBundle\MassEditAction\MassEditActionInterface');
    }

    function it_stores_the_family_to_add_the_products_to(Family $mugs)
    {
        $this->getFamily()->shouldReturn(null);

        $this->setFamily($mugs);

        $this->getFamily()->shouldReturn($mugs);
        $this->getFamily()->shouldBeAnInstanceOf('Pim\Bundle\CatalogBundle\Entity\Family');
    }

    function it_provides_a_form_type()
    {
        $this->getFormType()->shouldReturn('pim_enrich_mass_change_family');
    }

    function it_adds_products_to_the_selected_family_when_performimg_the_operation(
        $productRepository,
        AbstractQuery $query,
        Family $mugs,
        ProductInterface $product1,
        ProductInterface $product2
    ) {
        $productIds = array(1, 3);
        $productRepository->findBy(array('id' => $productIds))->willReturn([$product1, $product2]);

        $this->setFamily($mugs);

        $product1->setFamily($mugs)->shouldBeCalled();
        $product2->setFamily($mugs)->shouldBeCalled();

        $this->perform($productIds);
    }
}
