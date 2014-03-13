<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValue;
use Pim\Bundle\CatalogBundle\Model\Completeness;
use Pim\Bundle\CatalogBundle\Factory\CompletenessFactory;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\CatalogBundle\Entity\Currency;
use Pim\Bundle\CatalogBundle\Entity\Locale;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Entity\AttributeRequirement;

use Pim\Bundle\FlexibleEntityBundle\Entity\Price;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\DocumentManager;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CompletenessGeneratorSpec extends ObjectBehavior
{
    function let(
        DocumentManager $manager,
        CompletenessFactory $completenessFactory,
        ProductInterface $product,
        Family $family,
        Attribute $varchar,
        ProductValue $varcharValue,
        Attribute $price,
        ProductValue $priceValue,
        Price $priceEur,
        Price $priceUsd,
        Attribute $metric,
        ProductValue $metricValue,
        AttributeRequirement $requireVarcharEcommerce,
        AttributeRequirement $requireVarcharMobile,
        AttributeRequirement $requirePrice,
        Channel $ecommerce,
        Channel $mobile,
        Locale $enUs,
        Locale $frFr,
        Currency $usd,
        Currency $eur
    ) {
        $usd->getCode()->willReturn("USD");
        $eur->getCode()->willReturn("EUR");

        $varchar->getCode()->willReturn('attr_varchar');
        $varchar->getBackendType()->willReturn('varchar');
        $varcharValue->getAttribute()->willReturn($varchar);
        $varcharValue->getData()->willReturn('test_name');

        $priceEur->getCurrency()->willReturn($eur);
        $priceUsd->getCurrency()->willReturn($usd);

        $price->getCode()->willReturn('attr_price');
        $price->getBackendType()->willReturn('prices');
        $priceValue->getAttribute()->willReturn($price);

        $product->getFamily()->willReturn($family);

        $enUs->getCode()->willReturn('en_US');
        $frFr->getCode()->willReturn('fr_FR');

        $ecommerce->getCode()->willReturn('ecommerce');
        $ecommerce->getLocales()->willReturn(array($enUs, $frFr));

        $mobile->getCode()->willReturn('mobile');
        $mobile->getLocales()->willReturn(array($enUs, $frFr));

        $requirePrice->getAttribute()->willReturn($price);

        $requireVarcharEcommerce->getChannel()->willReturn($ecommerce);
        $requireVarcharEcommerce->getAttribute()->willReturn($varchar);

        $requireVarcharMobile->getChannel()->willReturn($mobile);
        $requireVarcharMobile->getAttribute()->willReturn($varchar);

        $this->beConstructedWith($manager, $completenessFactory);
    }

    /**
     * @require class Doctrine\ODM\MongoDB\DocumentManager
     */
    function it_is_a_completeness_generator()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Doctrine\CompletenessGeneratorInterface');
    }

    /**
     * @require class Doctrine\ODM\MongoDB\DocumentManager
     */
    function it_schedules_product_completeness(ProductInterface $product, DocumentManager $manager)
    {
        $manager->flush($product)->shouldBeCalled();
        $product->setCompletenesses(new ArrayCollection())->shouldBeCalled();

        $this->schedule($product);
    }

    /**
     * @require class Doctrine\ODM\MongoDB\DocumentManager
     */
    function it_builds_product_completenesses_with_scoped_localized_varchar(
        ProductInterface $product,
        ProductValue $varcharValue,
        CompletenessFactory $completenessFactory,
        Family $family,
        AttributeRequirement $requireVarcharEcommerce,
        Channel $ecommerce,
        Locale $frFr,
        Locale $enUs
    ) {
        $family->getAttributeRequirements()->willReturn(array($requireVarcharEcommerce));

        $product->getValue('attr_varchar', 'en_US', 'ecommerce')->willReturn($varcharValue);
        $product->getValue('attr_varchar', 'fr_FR', 'ecommerce')->willReturn(null);

        $completenessFactory->build($ecommerce, $enUs, 0, 1)->shouldBeCalled();
        $completenessFactory->build($ecommerce, $frFr, 1, 1)->shouldBeCalled();

        $this->buildProductCompletenesses($product);
    }

    /**
     * @require class Doctrine\ODM\MongoDB\DocumentManager
     */
    function it_builds_product_completenesses_with_scoped_localized_varchar_and_two_channels (
        ProductInterface $product,
        ProductValue $varcharValue,
        CompletenessFactory $completenessFactory,
        Family $family,
        AttributeRequirement $requireVarcharEcommerce,
        AttributeRequirement $requireVarcharMobile,
        Channel $ecommerce,
        Channel $mobile,
        Locale $frFr,
        Locale $enUs
    ) {
        $family->getAttributeRequirements()->willReturn(array($requireVarcharEcommerce, $requireVarcharMobile));

        $product->getValue('attr_varchar', 'en_US', 'ecommerce')->willReturn($varcharValue);
        $product->getValue('attr_varchar', 'fr_FR', 'ecommerce')->willReturn(null);
        $product->getValue('attr_varchar', 'en_US', 'mobile')->willReturn($varcharValue);
        $product->getValue('attr_varchar', 'fr_FR', 'mobile')->willReturn($varcharValue);

        $completenessFactory->build($ecommerce, $enUs, 0, 1)->shouldBeCalled();
        $completenessFactory->build($ecommerce, $frFr, 1, 1)->shouldBeCalled();
        $completenessFactory->build($mobile, $enUs, 0, 1)->shouldBeCalled();
        $completenessFactory->build($mobile, $frFr, 0, 1)->shouldBeCalled();

        $this->buildProductCompletenesses($product);
    }

    /**
     * @require class Doctrine\ODM\MongoDB\DocumentManager
     */
    function it_builds_product_completenesses_with_price_and_missing_currency (
        ProductInterface $product,
        ProductValue $priceValue,
        Price $priceEur,
        CompletenessFactory $completenessFactory,
        Family $family,
        AttributeRequirement $requirePrice,
        Channel $ecommerce,
        Locale $frFr,
        Locale $enUs,
        Currency $eur,
        Currency $usd
    ) {
        $requirePrice->getChannel()->willReturn($ecommerce);
        $family->getAttributeRequirements()->willReturn(array($requirePrice));

        $priceEur->getData()->willReturn('12.34');
        $priceValue->getPrices()->willReturn(array($priceEur));

        $product->getValue('attr_price', 'en_US', 'ecommerce')->willReturn($priceValue);
        $product->getValue('attr_price', 'fr_FR', 'ecommerce')->willReturn($priceValue);

        $ecommerce->getCurrencies()->willReturn(array($usd, $eur));

        $completenessFactory->build($ecommerce, $enUs, 1, 1)->shouldBeCalled();
        $completenessFactory->build($ecommerce, $frFr, 1, 1)->shouldBeCalled();

        $this->buildProductCompletenesses($product);
    }

    /**
     * @require class Doctrine\ODM\MongoDB\DocumentManager
     */
    function it_builds_product_completenesses_with_price_and_all_currencies_but_missing_data (
        ProductInterface $product,
        ProductValue $priceValue,
        CompletenessFactory $completenessFactory,
        Family $family,
        AttributeRequirement $requirePrice,
        Channel $ecommerce,
        Locale $frFr,
        Locale $enUs,
        Currency $eur,
        Currency $usd,
        Price $priceEur,
        Price $priceUsd
    ) {
        $requirePrice->getChannel()->willReturn($ecommerce);
        $family->getAttributeRequirements()->willReturn(array($requirePrice));

        $priceValue->getPrices()->willReturn(array($priceEur, $priceUsd));
        $priceEur->getData()->willReturn('12.34');
        $priceUsd->getData()->willReturn(null);

        $product->getValue('attr_price', 'en_US', 'ecommerce')->willReturn($priceValue);
        $product->getValue('attr_price', 'fr_FR', 'ecommerce')->willReturn($priceValue);

        $ecommerce->getCurrencies()->willReturn(array($usd, $eur));

        $completenessFactory->build($ecommerce, $enUs, 1, 1)->shouldBeCalled();
        $completenessFactory->build($ecommerce, $frFr, 1, 1)->shouldBeCalled();

        $this->buildProductCompletenesses($product);
    }

    /**
     * @require class Doctrine\ODM\MongoDB\DocumentManager
     */
    function it_builds_product_completenesses_with_price_and_all_currencies_and_data (
        ProductInterface $product,
        ProductValue $priceValue,
        CompletenessFactory $completenessFactory,
        Family $family,
        AttributeRequirement $requirePrice,
        Channel $ecommerce,
        Locale $frFr,
        Locale $enUs,
        Currency $eur,
        Currency $usd,
        Price $priceEur,
        Price $priceUsd
    ) {
        $requirePrice->getChannel()->willReturn($ecommerce);
        $family->getAttributeRequirements()->willReturn(array($requirePrice));

        $priceValue->getPrices()->willReturn(array($priceEur, $priceUsd));
        $priceEur->getData()->willReturn('12.34');
        $priceUsd->getData()->willReturn('13.45');

        $product->getValue('attr_price', 'en_US', 'ecommerce')->willReturn($priceValue);
        $product->getValue('attr_price', 'fr_FR', 'ecommerce')->willReturn($priceValue);

        $ecommerce->getCurrencies()->willReturn(array($usd, $eur));

        $completenessFactory->build($ecommerce, $enUs, 0, 1)->shouldBeCalled();
        $completenessFactory->build($ecommerce, $frFr, 0, 1)->shouldBeCalled();

        $this->buildProductCompletenesses($product);
    }
}
