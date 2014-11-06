<?php

namespace spec\Pim\Bundle\EnrichBundle\MassEditAction\Operation;

use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Context\CatalogContext;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;
use Pim\Bundle\CatalogBundle\Entity\Locale;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeRepository;
use Pim\Bundle\CatalogBundle\Manager\CurrencyManager;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Manager\ProductMassActionManager;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Model\AbstractProductValue;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Updater\ProductUpdaterInterface;
use Pim\Bundle\UserBundle\Context\UserContext;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class EditCommonAttributesSpec extends ObjectBehavior
{
    function let(
        ProductManager $productManager,
        ProductUpdaterInterface $productUpdater,
        UserContext $userContext,
        CurrencyManager $currencyManager,
        Locale $en,
        Locale $de,
        AttributeRepository $attributeRepository,
        AbstractProductValue $productValue,
        CatalogContext $catalogContext,
        ProductMassActionManager $massActionManager,
        NormalizerInterface $normalizer
    ) {
        $en->getCode()->willReturn('en_US');
        $de->getCode()->willReturn('de_DE');
        $userContext->getCurrentLocale()->willReturn($en);
        $userContext->getUserLocales()->willReturn([$en, $de]);

        $catalogContext->setLocaleCode(Argument::any())->willReturn($catalogContext);
        $productManager->createProductValue()->willReturn($productValue);

        $productValue->setAttribute(Argument::any())->willReturn($productValue);
        $productValue->setLocale(Argument::any())->willReturn($productValue);
        $productValue->setScope(Argument::any())->willReturn($productValue);
        $productValue->addPrice(Argument::any())->willReturn($productValue);

        $productManager->getAttributeRepository()->willReturn($attributeRepository);

        $this->beConstructedWith(
            $productManager,
            $productUpdater,
            $userContext,
            $currencyManager,
            $catalogContext,
            $massActionManager,
            $normalizer,
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

    function it_stores_the_locale_the_product_is_being_edited_in($en, Locale $fr)
    {
        $this->getLocale()->shouldBeAnInstanceOf('Pim\Bundle\CatalogBundle\Entity\Locale');
        $this->getLocale()->shouldReturn($en);

        $this->setLocale($fr);
        $this->getLocale()->shouldReturn($fr);
    }

    function it_stores_the_common_attributes_of_the_products()
    {
        $this->getCommonAttributes()->shouldReturn([]);
        $this->setCommonAttributes(['foo', 'bar', 'baz']);
        $this->getCommonAttributes()->shouldReturn(['foo', 'bar', 'baz']);
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

    function it_provides_form_options($en, $de)
    {
        $this->setCommonAttributes(['foo', 'bar', 'baz']);
        $this->getFormOptions()->shouldReturn([
            'locales' => [$en, $de],
            'common_attributes' => ['foo', 'bar', 'baz'],
            'current_locale' => 'en_US'
        ]);
    }

    function it_initializes_the_operation_with_common_attributes_of_the_products(
        ProductInterface $product1,
        ProductInterface $product2,
        AbstractAttribute $name,
        $massActionManager
    ) {
        $this->setObjectsToMassEdit([$product1, $product2]);

        $product1->getId()->willReturn(1);
        $product2->getId()->willReturn(2);

        $name->setLocale(Argument::any())->willReturn($name);
        $name->getAttributeType()->willReturn('pim_catalog_text');
        $name->isScopable()->willReturn(false);
        $name->isLocalizable()->willReturn(false);
        $name->getCode()->willReturn('name');
        $name->getGroup()->willReturn(new AttributeGroup());
        $name->getAvailableLocaleCodes()->willReturn(null);

        $massActionManager->findCommonAttributes([1, 2])->willReturn([$name]);

        $this->initialize();

        $this->getCommonAttributes()->shouldReturn([$name]);
        $this->getValues()->shouldHaveCount(1);
    }
}
