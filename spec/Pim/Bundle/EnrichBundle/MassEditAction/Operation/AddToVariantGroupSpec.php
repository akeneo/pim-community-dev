<?php

namespace spec\Pim\Bundle\EnrichBundle\MassEditAction\Operation;

use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Doctrine\ORM\AbstractQuery;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductTemplateInterface;
use Pim\Bundle\CatalogBundle\Repository\GroupRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductMassActionRepositoryInterface;
use Pim\Bundle\CatalogBundle\Updater\ProductTemplateUpdaterInterface;
use Prophecy\Argument;
use Symfony\Component\Validator\ValidatorInterface;

class AddToVariantGroupSpec extends ObjectBehavior
{
    function let(
        GroupRepositoryInterface $groupRepository,
        BulkSaverInterface $productSaver,
        GroupInterface $shirts,
        GroupInterface $pants,
        ValidatorInterface $validator,
        ProductMassActionRepositoryInterface $productMassActionRepo,
        ProductTemplateUpdaterInterface $productTemplateUpdater
    ) {
        $this->beConstructedWith(
            $groupRepository,
            $productSaver,
            $productTemplateUpdater,
            $validator,
            $productMassActionRepo
        );
    }

    function it_is_a_mass_edit_action()
    {
        $this->shouldImplement('Pim\Bundle\EnrichBundle\MassEditAction\Operation\MassEditOperationInterface');
    }

    function it_provides_a_form_type()
    {
        $this->getFormType()->shouldReturn('pim_enrich_mass_add_to_variant_group');
    }

    function it_provides_form_options(
        $groupRepository,
        $shirts,
        $pants,
        $productMassActionRepo,
        ProductInterface $product1
    ) {
        $commonAttributes = [];

        $productMassActionRepo->findCommonAttributeIds(Argument::type('array'))->willReturn($commonAttributes);
        $groupRepository->getVariantGroupsByAttributes($commonAttributes)->willReturn([$shirts, $pants]);

        $groupRepository->getAllVariantGroups()->willReturn([$shirts, $pants]);
        $this->setObjectsToMassEdit([$product1]);

        $this->getFormOptions()->shouldReturn(['groups' => [$shirts, $pants]]);
    }

    function it_adds_products_to_groups_when_performing_the_operation(
        $shirts,
        ProductInterface $product1,
        ProductInterface $product2,
        ProductTemplateInterface $shirtProductTemplate
    ) {
        $this->setObjectsToMassEdit([$product1, $product2]);

        $this->setGroup($shirts);

        $shirts->addProduct($product1)->shouldBeCalled();
        $shirts->addProduct($product2)->shouldBeCalled();

        $shirts->getProductTemplate()->willReturn($shirtProductTemplate);

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
        $productMassActionRepo,
        CustomGroupInterface $shoes,
        ProductInterface $product1,
        ProductInterface $product2
    ) {
        $commonAttributes = [];
        $shoes->__toString()->willReturn('shoes');

        $groupRepository->getAllVariantGroups()->willReturn([$shoes]);
        $productMassActionRepo->findCommonAttributeIds(Argument::type('array'))->willReturn($commonAttributes);
        $groupRepository->getVariantGroupsByAttributes($commonAttributes)->willReturn([$shoes]);

        $product1->getVariantGroup()->willReturn(null);
        $product1->getIdentifier()->shouldNotBeCalled();
        $product2->getVariantGroup()->willReturn($shoes);
        $product2->getIdentifier()->shouldBeCalled()->willReturn('shirt_000');

        $this->setObjectsToMassEdit([$product1, $product2]);

        $product1->getId()->willReturn(1);
        $product2->getId()->willReturn(2);
        $this->getFormOptions();

        $this->getWarningMessages()->shouldReturn([[
            'key'     => 'pim_enrich.mass_edit_action.add-to-variant-group.already_in_variant_group_or_not_valid',
            'options' => ['%products%' => 'shirt_000']
        ]]);
    }

    function it_applies_product_template_to_products_when_performing_the_operation(
        $shirts,
        ProductInterface $product1,
        ProductInterface $product2,
        ProductTemplateInterface $shirtProductTemplate
    ) {
        $this->setObjectsToMassEdit([$product1, $product2]);
        $this->setGroup($shirts);

        $shirts->getProductTemplate()->willReturn($shirtProductTemplate);

        $this->perform();
    }
}

interface CustomGroupInterface extends GroupInterface
{
    public function __toString();
}
