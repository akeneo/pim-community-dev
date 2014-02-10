<?php

namespace spec\Pim\Bundle\EnrichBundle\MassEditAction;

use PhpSpec\ObjectBehavior;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\AbstractQuery;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;

class ChangeStatusSpec extends ObjectBehavior
{
    function it_is_a_mass_edit_action()
    {
        $this->shouldBeAnInstanceOf('Pim\Bundle\EnrichBundle\MassEditAction\MassEditActionInterface');
    }

    function it_stores_the_desired_product_status()
    {
        $this->isToEnable()->shouldReturn(true);

        $this->setToEnable(false);
        $this->isToEnable()->shouldReturn(false);

        $this->setToEnable(true);
        $this->isToEnable()->shouldReturn(true);
    }

    function it_provides_a_form_type()
    {
        $this->getFormType()->shouldBeAnInstanceOf('Pim\Bundle\EnrichBundle\Form\Type\MassEditAction\ChangeStatusType');
    }

    function it_changes_the_status_of_the_products_when_performimg_the_operation(
        QueryBuilder $qb,
        AbstractQuery $query,
        ProductInterface $product2,
        ProductInterface $product1
    ) {
        $qb->getQuery()->willReturn($query);
        $query->getResult()->willReturn([$product1, $product2]);

        $this->setToEnable(false);
        $product1->setEnabled(false)->shouldBeCalled();
        $product2->setEnabled(false)->shouldBeCalled();
        $this->perform($qb);

        $this->setToEnable(true);
        $product1->setEnabled(true)->shouldBeCalled();
        $product2->setEnabled(true)->shouldBeCalled();
        $this->perform($qb);
    }
}
