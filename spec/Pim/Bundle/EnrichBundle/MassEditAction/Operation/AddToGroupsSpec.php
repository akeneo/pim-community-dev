<?php

namespace spec\Pim\Bundle\EnrichBundle\MassEditAction\Operation;

use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Doctrine\ORM\AbstractQuery;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Repository\GroupRepositoryInterface;

class AddToGroupsSpec extends ObjectBehavior
{
    function let(
        GroupRepositoryInterface $groupRepository,
        BulkSaverInterface $productSaver,
        GroupInterface $shirts,
        GroupInterface $pants
    ) {
        $this->beConstructedWith($groupRepository, $productSaver);
    }

    function it_is_a_mass_edit_action()
    {
        $this->shouldImplement('Pim\Bundle\EnrichBundle\MassEditAction\Operation\MassEditOperationInterface');
    }

    function it_provides_a_form_type()
    {
        $this->getFormType()->shouldReturn('pim_enrich_mass_add_to_groups');
    }

    function it_provides_form_options($groupRepository, $shirts, $pants)
    {
        $groupRepository->getAllGroupsExceptVariant()->willReturn([$shirts, $pants]);

        $this->getFormOptions()->shouldReturn(['groups' => [$shirts, $pants]]);
    }

    function it_adds_products_to_groups_when_performing_the_operation(
        AbstractQuery $query,
        ProductInterface $product1,
        ProductInterface $product2,
        $shirts,
        $pants
    ) {
        $this->setObjectsToMassEdit([$product1, $product2]);

        $this->setGroups([$shirts, $pants]);

        $shirts->addProduct($product1)->shouldBeCalled();
        $shirts->addProduct($product2)->shouldBeCalled();
        $pants->addProduct($product1)->shouldBeCalled();
        $pants->addProduct($product2)->shouldBeCalled();

        $this->perform();
    }
}
