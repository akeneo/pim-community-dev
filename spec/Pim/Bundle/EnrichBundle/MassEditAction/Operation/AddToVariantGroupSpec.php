<?php

namespace spec\Pim\Bundle\EnrichBundle\MassEditAction\Operation;

use Akeneo\Component\Persistence\BulkSaverInterface;
use Doctrine\ORM\AbstractQuery;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Pim\Bundle\CatalogBundle\Entity\Repository\GroupRepository;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;

class AddToVariantGroupSpec extends ObjectBehavior
{
    function let(
        GroupRepository $groupRepository,
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
        $this->getFormType()->shouldReturn('pim_enrich_mass_add_to_variant_group');
    }

    function it_provides_form_options($groupRepository, $shirts, $pants)
    {
        $groupRepository->getAllVariantGroups()->willReturn([$shirts, $pants]);

        $this->getFormOptions()->shouldReturn(['groups' => [$shirts, $pants]]);
    }

    function it_adds_products_to_groups_when_performing_the_operation(
        ProductInterface $product1,
        ProductInterface $product2,
        $shirts
    ) {
        $this->setObjectsToMassEdit([$product1, $product2]);

        $this->setGroup($shirts);

        $shirts->addProduct($product1)->shouldBeCalled();
        $shirts->addProduct($product2)->shouldBeCalled();

        $this->perform();
    }

    function it_generates_warning_message_if_there_is_no_variant_group(
        $groupRepository,
        ProductInterface $product1,
        ProductInterface $product2
    ) {
        $groupRepository->getAllVariantGroups()->willReturn([]);
        $this->setObjectsToMassEdit([$product1, $product2]);

        $this->getWarningMessages()->shouldReturn([[
            'key'     => 'pim_enrich.mass_edit_action.add-to-variant-group.no_variant_group',
            'options' => []
        ]]);
    }

    function it_generates_warning_message_if_there_is_products_in_a_variant_group(
        $groupRepository,
        $shirts,
        ProductInterface $product1,
        ProductInterface $product2
    ) {
        $groupRepository->getAllVariantGroups()->willReturn([$shirts]);
        $this->setObjectsToMassEdit([$product1, $product2]);

        $product1->getVariantGroup()->willReturn(null);
        $product1->getIdentifier()->shouldNotBeCalled();
        $product2->getVariantGroup()->willReturn($shirts);
        $product2->getIdentifier()->shouldBeCalled()->willReturn('shirt_000');

        $this->getWarningMessages()->shouldReturn([[
            'key'     => 'pim_enrich.mass_edit_action.add-to-variant-group.already_in_variant_group',
            'options' => ['%products%' => 'shirt_000']
        ]]);
    }
}
