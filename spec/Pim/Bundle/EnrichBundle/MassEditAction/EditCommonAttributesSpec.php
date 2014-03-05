<?php

namespace spec\Pim\Bundle\EnrichBundle\MassEditAction;

use Pim\Bundle\CatalogBundle\Entity\Family;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\AbstractQuery;
use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Manager\CurrencyManager;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;
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
        $qb->getRootAliases()->willReturn(['p']);
        $qb->select(Argument::any())->willReturn($qb);
        $qb->groupBy(Argument::any())->willReturn($qb);

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
        $productManager,
        $qb
    ) {
        $query->getResult()->willReturn([$product1, $product2]);

        $product1->getId()->willReturn(1);
        $product2->getId()->willReturn(2);

        $name->setLocale(Argument::any())->willReturn($name);
        $name->getAttributeType()->willReturn('pim_catalog_text');
        $name->isScopable()->willReturn(false);
        $name->isLocalizable()->willReturn(false);
        $name->getCode()->willReturn('name');
        $name->getVirtualGroup()->willReturn(new AttributeGroup());

        $productManager->findCommonAttributes([1,2])->willReturn([$name]);

        $this->initialize($qb);

        $this->getCommonAttributes()->shouldReturn([$name]);
        $this->getValues()->shouldHaveCount(1);
    }

    function it_updates_the_products_when_performimg_the_operation(
        $qb,
        $query,
        Product $product1,
        Product $product2,
        Attribute $attribute,
        $productManager,
        $productValue
    ) {
        $query->getResult()->willReturn([$product1, $product2]);

        $product1->getId()->willReturn(1);
        $product2->getId()->willReturn(2);

        $attribute->setLocale(Argument::any())->willReturn($attribute);
        $attribute->getAttributeType()->willReturn('pim_catalog_text');
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getCode()->willReturn('attribute');
        $attribute->getVirtualGroup()->willReturn(new AttributeGroup());

        $productManager->findCommonAttributes([1,2])->willReturn([$attribute]);
        $productValue->getAttribute()->willReturn($attribute);

        $this->initialize($qb);
        $this->setDisplayedAttributes(new ArrayCollection([$attribute]));

        $this->getValues()->shouldHaveCount(1);

        $productManager->handleAllMedia([$product1, $product2])->shouldBeCalled();

        $this->perform($qb);
    }
}
