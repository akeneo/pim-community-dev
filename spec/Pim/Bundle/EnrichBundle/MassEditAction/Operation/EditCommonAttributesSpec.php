<?php

namespace spec\Pim\Bundle\EnrichBundle\MassEditAction\Operation;

use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilder;
use Pim\Bundle\CatalogBundle\Context\CatalogContext;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;
use Pim\Bundle\CatalogBundle\Model\LocaleInterface;
use Pim\Bundle\CatalogBundle\Manager\CurrencyManager;
use Pim\Bundle\CatalogBundle\Manager\ProductMassActionManager;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductTemplateInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Updater\ProductUpdaterInterface;
use Pim\Bundle\UserBundle\Context\UserContext;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class EditCommonAttributesSpec extends ObjectBehavior
{
    function let(
        ProductBuilder $productBuilder,
        ProductUpdaterInterface $productUpdater,
        UserContext $userContext,
        LocaleInterface $en,
        LocaleInterface $de,
        ProductValueInterface $productValue,
        CatalogContext $catalogContext,
        ProductMassActionManager $massActionManager,
        NormalizerInterface $normalizer,
        BulkSaverInterface $productSaver
    ) {
        $en->getCode()->willReturn('en_US');
        $de->getCode()->willReturn('de_DE');
        $userContext->getCurrentLocale()->willReturn($en);
        $userContext->getUserLocales()->willReturn([$en, $de]);

        $catalogContext->setLocaleCode(Argument::any())->willReturn($catalogContext);

        $productValue->setAttribute(Argument::any())->willReturn($productValue);
        $productValue->setLocale(Argument::any())->willReturn($productValue);
        $productValue->setScope(Argument::any())->willReturn($productValue);
        $productValue->addPrice(Argument::any())->willReturn($productValue);

        $this->beConstructedWith(
            $productBuilder,
            $productUpdater,
            $userContext,
            $catalogContext,
            $massActionManager,
            $normalizer,
            $productSaver,
            [
                'product_price' => 'Pim\Bundle\CatalogBundle\Model\ProductPrice',
                'product_media' => 'Pim\Bundle\CatalogBundle\Model\ProductMedia'
            ]
        );
    }

    function it_is_a_mass_edit_action()
    {
        $this->shouldImplement('Pim\Bundle\EnrichBundle\MassEditAction\Operation\MassEditOperationInterface');
    }

    function it_stores_the_desired_product_values()
    {
        $this->getValues()->shouldBeAnInstanceOf('Doctrine\Common\Collections\ArrayCollection');
        $this->getValues()->shouldBeEmpty();

        $this->setValues(new ArrayCollection(['foo', 'bar']));
        $this->getValues()->shouldBeAnInstanceOf('Doctrine\Common\Collections\ArrayCollection');
        $this->getValues()->shouldHaveCount(2);
    }

    function it_stores_the_locale_the_product_is_being_edited_in($en, LocaleInterface $fr)
    {
        $this->getLocale()->shouldReturn($en);

        $this->setLocale($fr);
        $this->getLocale()->shouldReturn($fr);
    }

    function it_stores_the_attributes_displayed_by_the_user()
    {
        $this->getDisplayedAttributes()->shouldBeAnInstanceOf('Doctrine\Common\Collections\ArrayCollection');
        $this->getDisplayedAttributes()->shouldBeEmpty();

        $displayedAttributes = new ArrayCollection(['foo', 'bar', 'baz']);
        $this->setDisplayedAttributes($displayedAttributes);
        $this->getDisplayedAttributes()->shouldReturn($displayedAttributes);
    }

    function it_provides_a_form_type()
    {
        $this->getFormType()->shouldReturn('pim_enrich_mass_edit_common_attributes');
    }

    function it_provides_form_options($en, $de, $massActionManager, AttributeInterface $name, AttributeInterface $description, AttributeInterface $price, AttributeGroup $otherGroup, $massActionManager)
    {
        $this->setObjectsToMassEdit(['foo', 'bar', 'baz']);

        $massActionManager->findCommonAttributes(['foo', 'bar', 'baz'])->willReturn([$name, $description, $price]);

        $name->getGroup()->willReturn($otherGroup);
        $name->setLocale('en_US')->shouldBeCalled();
        $otherGroup->setLocale('en_US')->shouldBeCalled();
        $description->getGroup()->willReturn($otherGroup);
        $description->setLocale('en_US')->shouldBeCalled();
        $price->getGroup()->willReturn($otherGroup);
        $price->setLocale('en_US')->shouldBeCalled();

        $massActionManager->filterLocaleSpecificAttributes([$name, $description, $price], 'en_US')->willReturn([$name, $description, $price]);
        $massActionManager->filterAttributesComingFromVariant([$name, $description, $price], ['foo', 'bar', 'baz'])->willReturn([$name, $description, $price]);

        $this->getFormOptions()->shouldReturn([
            'locales' => [$en, $de],
            'common_attributes' => [$name, $description, $price],
            'current_locale' => 'en_US'
        ]);
    }

    function it_initializes_the_operation_with_common_attributes_of_the_products(
        $massActionManager,
        $productBuilder,
        ProductInterface $product1,
        ProductInterface $product2,
        AttributeInterface $name,
        AttributeInterface $name,
        AttributeInterface $description,
        AttributeInterface $price,
        AttributeGroup $otherGroup,
        ProductValueInterface $nameProductValue,
        ProductValueInterface $descriptionProductValue,
        ProductValueInterface $priceProductValue
    ) {
        $this->setObjectsToMassEdit([$product1, $product2]);

        $name->getGroup()->willReturn($otherGroup);
        $name->setLocale('en_US')->shouldBeCalled();
        $otherGroup->setLocale('en_US')->shouldBeCalled();
        $description->getGroup()->willReturn($otherGroup);
        $description->setLocale('en_US')->shouldBeCalled();
        $price->getGroup()->willReturn($otherGroup);
        $price->setLocale('en_US')->shouldBeCalled();

        $product1->getId()->willReturn(1);
        $product2->getId()->willReturn(2);

        $name->setLocale(Argument::any())->willReturn($name);
        $name->isScopable()->willReturn(false);
        $name->getCode()->willReturn('name');
        $description->setLocale(Argument::any())->willReturn($description);
        $description->isScopable()->willReturn(false);
        $description->getCode()->willReturn('description');
        $price->setLocale(Argument::any())->willReturn($price);
        $price->isScopable()->willReturn(false);
        $price->getCode()->willReturn('price');

        $this->setObjectsToMassEdit([$product1, $product2]);

        $massActionManager->findCommonAttributes([$product1, $product2])->willReturn([$name, $description, $price]);
        $massActionManager->filterLocaleSpecificAttributes([$name, $description, $price], 'en_US')->willReturn([$name, $description, $price]);
        $massActionManager->filterAttributesComingFromVariant([$name, $description, $price], [$product1, $product2])->willReturn([$name, $description, $price]);

        $productBuilder->createProductValue($name, 'en_US')->willReturn($nameProductValue);
        $productBuilder->addMissingPrices($nameProductValue)->shouldBeCalled();
        $productBuilder->createProductValue($description, 'en_US')->willReturn($descriptionProductValue);
        $productBuilder->addMissingPrices($descriptionProductValue)->shouldBeCalled();
        $productBuilder->createProductValue($price, 'en_US')->willReturn($priceProductValue);
        $productBuilder->addMissingPrices($priceProductValue)->shouldBeCalled();


        $this->initialize();

        $this->getCommonAttributes()->shouldReturn([$name, $description, $price]);
        $this->getValues()->shouldHaveCount(3);
    }

    function it_filters_attributes_coming_from_variant_group(
        $massActionManager,
        $productBuilder,
        ProductInterface $product1,
        ProductInterface $product2,
        AttributeInterface $name,
        AttributeInterface $name,
        AttributeInterface $description,
        AttributeInterface $price,
        AttributeGroup $otherGroup,
        ProductValueInterface $nameProductValue,
        ProductValueInterface $descriptionProductValue,
        ProductValueInterface $priceProductValue
    ) {
        $this->setObjectsToMassEdit([$product1, $product2]);

        $name->getGroup()->willReturn($otherGroup);
        $name->setLocale('en_US')->shouldBeCalled();
        $otherGroup->setLocale('en_US')->shouldBeCalled();
        $description->getGroup()->willReturn($otherGroup);
        $description->setLocale('en_US')->shouldBeCalled();
        $price->getGroup()->willReturn($otherGroup);
        $price->setLocale('en_US')->shouldBeCalled();

        $product1->getId()->willReturn(1);
        $product2->getId()->willReturn(2);

        $name->setLocale(Argument::any())->willReturn($name);
        $name->isScopable()->willReturn(false);
        $name->getCode()->willReturn('name');
        $description->setLocale(Argument::any())->willReturn($description);
        $description->isScopable()->willReturn(false);
        $description->getCode()->willReturn('description');
        $price->setLocale(Argument::any())->willReturn($price);
        $price->isScopable()->willReturn(false);
        $price->getCode()->willReturn('price');

        $this->setObjectsToMassEdit([$product1, $product2]);

        $massActionManager->findCommonAttributes([$product1, $product2])->willReturn([$name, $description, $price]);
        $massActionManager->filterLocaleSpecificAttributes([$name, $description, $price], 'en_US')->willReturn([$name, $description, $price]);
        $massActionManager->filterAttributesComingFromVariant([$name, $description, $price], [$product1, $product2])->willReturn([$name, $price]);

        $productBuilder->createProductValue($name, 'en_US')->willReturn($nameProductValue);
        $productBuilder->addMissingPrices($nameProductValue)->shouldBeCalled();
        $productBuilder->createProductValue($price, 'en_US')->willReturn($priceProductValue);
        $productBuilder->addMissingPrices($priceProductValue)->shouldBeCalled();

        $this->initialize();

        $this->getCommonAttributes()->shouldReturn([$name, $price]);
        $this->getValues()->shouldHaveCount(2);
    }
}
