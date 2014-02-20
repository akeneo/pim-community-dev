<?php

namespace spec\Pim\Bundle\EnrichBundle\MassEditAction;

use PhpSpec\ObjectBehavior;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\AbstractQuery;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Entity\Family;

class ChangeFamilySpec extends ObjectBehavior
{
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
        $this->getFormType()->shouldBeAnInstanceOf('Pim\Bundle\EnrichBundle\Form\Type\MassEditAction\ChangeFamilyType');
    }

    function it_adds_products_to_the_selected_family_when_performimg_the_operation(
        QueryBuilder $qb,
        AbstractQuery $query,
        Family $mugs,
        ProductInterface $product2,
        ProductInterface $product1
    ) {
        $qb->getQuery()->willReturn($query);
        $query->getResult()->willReturn([$product1, $product2]);

        $this->setFamily($mugs);

        $product1->setFamily($mugs)->shouldBeCalled();
        $product2->setFamily($mugs)->shouldBeCalled();

        $this->perform($qb);
    }
}
