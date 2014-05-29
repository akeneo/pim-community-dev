<?php

namespace spec\Pim\Bundle\EnrichBundle\MassEditAction\Operation;

use PhpSpec\ObjectBehavior;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\AbstractQuery;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;

class ChangeFamilySpec extends ObjectBehavior
{
    function it_is_a_mass_edit_action()
    {
        $this->shouldImplement('Pim\Bundle\EnrichBundle\MassEditAction\Operation\MassEditOperationInterface');
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
        AbstractQuery $query,
        Family $mugs,
        ProductInterface $product1,
        ProductInterface $product2
    ) {
        $this->setFamily($mugs);
        $this->setObjectsToMassEdit([$product1, $product2]);

        $product1->setFamily($mugs)->shouldBeCalled();
        $product2->setFamily($mugs)->shouldBeCalled();

        $this->perform();
    }
}
