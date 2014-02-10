<?php

namespace spec\Pim\Bundle\EnrichBundle\MassEditAction;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\AbstractQuery;
use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Manager\CurrencyManager;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Pim\Bundle\CatalogBundle\Entity\Locale;
use Pim\Bundle\CatalogBundle\Model\Product;
use Pim\Bundle\CatalogBundle\Model\ProductValue;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeRepository;

class EditCommonAttributesSpec extends ObjectBehavior
{
    function let(
        ProductManager $productManager,
        UserContext $userContext,
        CurrencyManager $currencyManager,
        Locale $en,
        Locale $de,
        QueryBuilder $qb,
        AbstractQuery $query,
        AttributeRepository $attributeRepository,
        ProductValue $productValue
    ) {
        $en->getCode()->willReturn('en_US');
        $de->getCode()->willReturn('de_DE');
        $userContext->getCurrentLocale()->willReturn($en);
        $userContext->getUserLocales()->willReturn([$en, $de]);

        $qb->getQuery()->willReturn($query);

        $productManager->setLocale(Argument::any())->willReturn($productManager);
        $productManager->createProductValue()->willReturn($productValue);

        $productValue->setAttribute(Argument::any())->willReturn($productValue);
        $productValue->setLocale(Argument::any())->willReturn($productValue);
        $productValue->setScope(Argument::any())->willReturn($productValue);
        $productValue->addPrice(Argument::any())->willReturn($productValue);

        $productManager->getAttributeRepository()->willReturn($attributeRepository);

        $this->beConstructedWith($productManager, $userContext, $currencyManager);
    }

    function it_is_a_mass_edit_action()
    {
        $this->shouldBeAnInstanceOf('Pim\Bundle\EnrichBundle\MassEditAction\MassEditActionInterface');
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
        $this->getFormOptions()->shouldReturn(['locales' => [$en, $de], 'commonAttributes' => ['foo', 'bar', 'baz']]);
    }

    function it_initializes_the_operation_with_common_attributes_of_the_products(
        $query,
        Product $product1,
        Product $product2,
        Attribute $name,
        $attributeRepository,
        $qb
    ) {
        $query->getResult()->willReturn([$product1, $product2]);

        $product1->hasAttribute(Argument::any())->willReturn(true);
        $product2->hasAttribute(Argument::any())->willReturn(true);

        $name->setLocale(Argument::any())->willReturn($name);
        $name->getAttributeType()->willReturn('pim_catalog_text');
        $name->isUnique()->willReturn(false);
        $name->isScopable()->willReturn(false);
        $name->isLocalizable()->willReturn(false);
        $name->getCode()->willReturn('name');

        $attributeRepository->findAll()->willReturn([$name]);

        $this->initialize($qb);

        $this->getCommonAttributes()->shouldReturn([$name]);
        $this->getValues()->shouldHaveCount(1);
    }

    function it_does_not_allow_editing_identifier_attributes(
        $query,
        Product $product,
        Attribute $identifier,
        $attributeRepository,
        $qb
    ) {
        $query->getResult()->willReturn([$product]);

        $identifier->getAttributeType()->willReturn('pim_catalog_identifier');
        $attributeRepository->findAll()->willReturn([$identifier]);

        $this->initialize($qb);

        $this->getCommonAttributes()->shouldHaveCount(0);
        $this->getValues()->shouldHaveCount(0);
    }

    function it_does_not_allow_editing_unique_attributes(
        $query,
        Product $product,
        Attribute $attribute,
        $attributeRepository,
        $qb
    ) {
        $query->getResult()->willReturn([$product]);

        $attribute->getAttributeType()->willReturn('pim_catalog_text');
        $attribute->isUnique()->willReturn(true);
        $attributeRepository->findAll()->willReturn([$attribute]);

        $this->initialize($qb);

        $this->getCommonAttributes()->shouldHaveCount(0);
        $this->getValues()->shouldHaveCount(0);
    }

    function it_allows_editing_only_the_common_attributes(
        $query,
        Product $product1,
        Product $product2,
        Attribute $name,
        Attribute $color,
        Attribute $price,
        $attributeRepository,
        $qb
    ) {
        $query->getResult()->willReturn([$product1, $product2]);

        $product1->hasAttribute(Argument::any())->willReturn(true);
        $product2->hasAttribute(Argument::not($price))->willReturn(true);
        $product2->hasAttribute($price)->willReturn(false);

        $name->setLocale(Argument::any())->willReturn($name);
        $name->getAttributeType()->willReturn('pim_catalog_text');
        $name->isUnique()->willReturn(false);
        $name->isScopable()->willReturn(false);
        $name->isLocalizable()->willReturn(false);
        $name->getCode()->willReturn('name');

        $color->setLocale(Argument::any())->willReturn($color);
        $color->getAttributeType()->willReturn('pim_catalog_simpleselect');
        $color->isUnique()->willReturn(false);
        $color->isScopable()->willReturn(false);
        $color->isLocalizable()->willReturn(false);
        $color->getCode()->willReturn('color');

        $attributeRepository->findAll()->willReturn([$name, $color, $price]);

        $this->initialize($qb);

        $this->getCommonAttributes()->shouldReturn([$name, $color]);
        $this->getValues()->shouldHaveCount(2);
    }

    function it_updates_the_products_when_performimg_the_operation(
        $qb,
        $query,
        Product $product1,
        Product $product2,
        Attribute $attribute,
        $attributeRepository,
        $productManager,
        $productValue
    ) {
        $query->getResult()->willReturn([$product1, $product2]);

        $product1->hasAttribute($attribute)->willReturn(true);
        $product2->hasAttribute($attribute)->willReturn(true);

        $attribute->setLocale(Argument::any())->willReturn($attribute);
        $attribute->getAttributeType()->willReturn('pim_catalog_text');
        $attribute->isUnique()->willReturn(false);
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getCode()->willReturn('attribute');

        $attributeRepository->findAll()->willReturn([$attribute]);
        $productValue->getAttribute()->willReturn($attribute);

        $this->initialize($qb);
        $this->setDisplayedAttributes(new ArrayCollection([$attribute]));

        $this->getValues()->shouldHaveCount(1);

        $productManager->handleAllMedia([$product1, $product2])->shouldBeCalled();

        $this->perform($qb);
    }
}
