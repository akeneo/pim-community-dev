<?php

namespace spec\Pim\Bundle\EnrichBundle\MassEditAction\Operation;

use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Doctrine\ORM\AbstractQuery;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\FamilyInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;

class ChangeFamilySpec extends ObjectBehavior
{
    function let(BulkSaverInterface $productSaver)
    {
        $this->beConstructedWith($productSaver);
    }

    function it_is_a_mass_edit_action()
    {
        $this->shouldImplement('Pim\Bundle\EnrichBundle\MassEditAction\Operation\MassEditOperationInterface');
    }

    function it_stores_the_family_to_add_the_products_to(FamilyInterface $mugs)
    {
        $this->getFamily()->shouldReturn(null);

        $this->setFamily($mugs);

        $this->getFamily()->shouldReturn($mugs);
        $this->getFamily()->shouldBeAnInstanceOf('Pim\Bundle\CatalogBundle\Model\FamilyInterface');
    }

    function it_provides_a_form_type()
    {
        $this->getFormType()->shouldReturn('pim_enrich_mass_change_family');
    }

    function it_adds_products_to_the_selected_family_when_performing_the_operation(
        AbstractQuery $query,
        FamilyInterface $mugs,
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
