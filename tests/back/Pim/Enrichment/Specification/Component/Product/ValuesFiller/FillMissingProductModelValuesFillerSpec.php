<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\ValuesFiller;

use Akeneo\Channel\Component\Model\Channel;
use Akeneo\Channel\Component\Model\Currency;
use Akeneo\Channel\Component\Model\Locale;
use Akeneo\Channel\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\ValuesFiller\FillMissingProductModelValuesFiller;
use Akeneo\Pim\Structure\Component\Model\Family;
use Akeneo\Pim\Structure\Component\Model\FamilyVariant;
use Akeneo\Pim\Structure\Component\Model\VariantAttributeSet;
use Akeneo\Test\Common\Structure\Attribute\Builder;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;

class FillMissingProductModelValuesFillerSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $familyVariantRepository,
        ChannelRepositoryInterface $channelRepository,
        LocaleRepositoryInterface $localeRepository
    ) {
        $sku = (new Builder())->aIdentifier()->withCode('sku')->build();
        $name = (new Builder())->aTextAttribute()->withCode('name')->build();
        $localizableName = (new Builder())->aTextAttribute()->withCode('localizable_name')->localizable()->build();
        $scopableName = (new Builder())->aTextAttribute()->withCode('scopable_name')->scopable()->build();
        $scopableLocalizableName = (new Builder())->aTextAttribute()->withCode('scopable_localizable_name')->scopable()->localizable()->build();
        $price = (new Builder())->aPriceCollectionAttribute()->withCode('price')->build();
        $localizablePrice = (new Builder())->aPriceCollectionAttribute()->withCode('localizable_price')->localizable()->build();
        $scopablePrice = (new Builder())->aPriceCollectionAttribute()->withCode('scopable_price')->scopable()->build();
        $scopableLocalizablePrice = (new Builder())->aPriceCollectionAttribute()->withCode('scopable_localizable_price')->scopable()->localizable()->build();

        $family = new Family();
        $family
            ->addAttribute($sku)
            ->addAttribute($name)
            ->addAttribute($localizableName)
            ->addAttribute($scopableName)
            ->addAttribute($scopableLocalizableName)
            ->addAttribute($price)
            ->addAttribute($localizablePrice)
            ->addAttribute($scopablePrice)
            ->addAttribute($scopableLocalizablePrice);

        // common attributes: name, scopable_localizable_name
        // level 1 attributes: localizable_name, scopable_name
        // level 2 (variant product) attributes: sku, price, localizable_price, scopable_price, scopable_localizable_price
        $familyVariantWithoutPrices = new FamilyVariant();
        $familyVariantWithoutPrices->setFamily($family);
        $productAttributeSet = new VariantAttributeSet();
        $productAttributeSet->setLevel(2);
        $productAttributeSet->setAttributes([$sku, $price, $localizablePrice, $scopablePrice, $scopableLocalizablePrice]);
        $familyVariantWithoutPrices->addVariantAttributeSet($productAttributeSet);
        $subProductModelAttributeSet = new VariantAttributeSet();
        $subProductModelAttributeSet->setLevel(1);
        $subProductModelAttributeSet->setAttributes([$scopableName, $localizableName]);
        $familyVariantWithoutPrices->addVariantAttributeSet($subProductModelAttributeSet);
        $familyVariantRepository->findOneByIdentifier('without_prices')->willReturn($familyVariantWithoutPrices);

        // common attributes: price, localizable_price, scopable_price, scopable_localizable_price
        // level 1 (variant product) attributes: sku, name, localizable_name, scopable_name, scopable_localizable_name
        $familyVariantWithPrices = new FamilyVariant();
        $familyVariantWithPrices->setFamily($family);
        $attributeSet = new VariantAttributeSet();
        $attributeSet->setLevel(1);
        $attributeSet->setAttributes([$sku, $name, $localizableName, $scopableName, $scopableLocalizableName]);
        $familyVariantWithPrices->addVariantAttributeSet($attributeSet);
        $familyVariantRepository->findOneByIdentifier('with_prices')->willReturn($familyVariantWithPrices);

        $deDe = (new Locale())->setCode('de_DE');
        $enUs = (new Locale())->setCode('en_US');
        $frFR = (new Locale())->setCode('fr_FR');

        $USD = (new Currency())->setCode('USD');
        $EUR = (new Currency())->setCode('EUR');
        $AED = (new Currency())->setCode('AED');

        $tablet = (new Channel())->setCode('tablet');
        $tablet->setLocales([$enUs, $frFR]);
        $tablet->setCurrencies([$AED, $EUR]);

        $ecommerce = (new Channel())->setCode('ecommerce');
        $ecommerce->setLocales([$frFR, $deDe]);
        $ecommerce->setCurrencies([$USD, $EUR]);

        $channelRepository->findAll()->willReturn([$tablet, $ecommerce]);
        $localeRepository->getActivatedLocales()->willReturn([$enUs, $frFR, $deDe]);

        $this->beConstructedWith($familyVariantRepository, $channelRepository, $localeRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(FillMissingProductModelValuesFiller::class);
    }

    function it_creates_missing_values_for_a_root_product_model()
    {
        $this->fromStandardFormat(
            [
                'family_variant' => 'without_prices',
                'parent' => null,
                'values' => [],
            ]
        )->shouldBeLike(
            [
                'family_variant' => 'without_prices',
                'parent' => null,
                'values' => [
                    'name' => [
                        ['scope' => null, 'locale' => null, 'data' => null],
                    ],
                    'scopable_localizable_name' => [
                        ['scope' => 'tablet', 'locale' => 'en_US', 'data' => null],
                        ['scope' => 'tablet', 'locale' => 'fr_FR', 'data' => null],
                        ['scope' => 'ecommerce', 'locale' => 'fr_FR', 'data' => null],
                        ['scope' => 'ecommerce', 'locale' => 'de_DE', 'data' => null],
                    ],
                ],
            ]
        );
    }

    function it_creates_missing_values_for_a_sub_product_model()
    {
        $this->fromStandardFormat(
            [
                'family_variant' => 'without_prices',
                'parent' => 'a_root_product_model',
                'values' => [],
            ]
        )->shouldBeLike(
            [

                'family_variant' => 'without_prices',
                'parent' => 'a_root_product_model',
                'values' => [
                    'name' => [
                        ['scope' => null, 'locale' => null, 'data' => null],
                    ],
                    'scopable_localizable_name' => [
                        ['scope' => 'tablet', 'locale' => 'en_US', 'data' => null],
                        ['scope' => 'tablet', 'locale' => 'fr_FR', 'data' => null],
                        ['scope' => 'ecommerce', 'locale' => 'fr_FR', 'data' => null],
                        ['scope' => 'ecommerce', 'locale' => 'de_DE', 'data' => null],
                    ],
                    'localizable_name' => [
                        ['scope' => null, 'locale' => 'en_US', 'data' => null],
                        ['scope' => null, 'locale' => 'fr_FR', 'data' => null],
                        ['scope' => null, 'locale' => 'de_DE', 'data' => null],
                    ],
                    'scopable_name' => [
                        ['scope' => 'tablet', 'locale' => null, 'data' => null],
                        ['scope' => 'ecommerce', 'locale' => null, 'data' => null],
                    ],
                ],
            ]
        );
    }

    function it_does_not_replace_existing_values()
    {
        $this->fromStandardFormat(
            [
                'family_variant' => 'without_prices',
                'parent' => 'a_root_product_model',
                'values' => [
                    'name' => [
                        ['scope' => null, 'locale' => null, 'data' => 'foo'],
                    ],
                    'scopable_localizable_name' => [
                        ['scope' => 'tablet', 'locale' => 'fr_FR', 'data' => 'foo'],
                    ],
                    'localizable_name' => [
                        ['scope' => null, 'locale' => 'en_US', 'data' => 'foo'],
                    ],
                    'scopable_name' => [
                        ['scope' => 'tablet', 'locale' => null, 'data' => 'foo'],
                    ],
                ],
            ]
        )->shouldBeLike(
            [
                'family_variant' => 'without_prices',
                'parent' => 'a_root_product_model',
                'values' => [
                    'name' => [
                        ['scope' => null, 'locale' => null, 'data' => 'foo'],
                    ],
                    'scopable_localizable_name' => [
                        ['scope' => 'tablet', 'locale' => 'en_US', 'data' => null],
                        ['scope' => 'tablet', 'locale' => 'fr_FR', 'data' => 'foo'],
                        ['scope' => 'ecommerce', 'locale' => 'fr_FR', 'data' => null],
                        ['scope' => 'ecommerce', 'locale' => 'de_DE', 'data' => null],
                    ],
                    'localizable_name' => [
                        ['scope' => null, 'locale' => 'en_US', 'data' => 'foo'],
                        ['scope' => null, 'locale' => 'fr_FR', 'data' => null],
                        ['scope' => null, 'locale' => 'de_DE', 'data' => null],
                    ],
                    'scopable_name' => [
                        ['scope' => 'tablet', 'locale' => null, 'data' => 'foo'],
                        ['scope' => 'ecommerce', 'locale' => null, 'data' => null],
                    ],
                ],
            ]
        );
    }
//
//    function it_creates_empty_price_values()
//    {
//
//    }
//
//    function it_does_not_replace_existing_priice_values()
//    {
//
//    }
}
