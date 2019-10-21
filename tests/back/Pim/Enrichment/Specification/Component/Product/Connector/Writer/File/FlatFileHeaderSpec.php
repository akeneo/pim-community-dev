<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\File;

use  Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\File\FlatFileHeader;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class FlatFileHeaderSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beConstructedWith('my_code');

        $this->shouldHaveType(FlatFileHeader::class);
    }

    function it_can_be_built_from_attribute_data()
    {
        $attributeData = [
            'description',
            'pim_catalog_textarea',
            true,
            'ecommerce',
            true,
            ['en_US', 'de_DE'],
            ['USD', 'EUR'],
            ['USD', 'EUR', 'CNY'],
            []
        ];

        $this->beConstructedThrough('buildFromAttributeData', $attributeData);

        $this->shouldHaveType(FlatFileHeader::class);
    }

    function it_can_be_built_from_attribute_data_and_be_a_media()
    {
        $attributeData = [
            'description',
            'pim_catalog_image',
            true,
            'ecommerce',
            true,
            ['en_US', 'de_DE'],
            ['USD', 'EUR'],
            ['USD', 'EUR', 'CNY'],
            []
        ];

        $this->beConstructedThrough('buildFromAttributeData', $attributeData);

        $this->isMedia()->shouldReturn(true);
    }

    function it_must_properly_indicate_if_it_is_a_media()
    {
        $this->beConstructedWith(
            $code = 'my_code',
            $isScopable = false,
            $channelCode = null,
            $isLocalizable = false,
            $localeCodes = ['en_US', 'fr_FR'],
            $isMedia = true
        );

        $this->isMedia()->shouldReturn(true);
    }

    function it_must_properly_indicate_if_it_is_not_a_media()
    {
        $this->beConstructedWith($code = 'my_code');

        $this->isMedia()->shouldReturn(false);
    }

    function it_must_be_throw_an_exception_when_missing_specific_to_locales_list()
    {
        $this->shouldThrow('\InvalidArgumentException')->during(
            '__construct',
            [
                $code = 'my_code',
                $isScopable = false,
                $channelCode = null,
                $isLocalizable = false,
                $localeCodes = ['en_US', 'fr_FR'],
                $isMedia = true,
                $usesUnit = false,
                $usesCurrencies = false,
                $channelCurrencies = null,
                $allCurrencies = null,
                $isLocaleSpecific = true
            ]
        );
    }

    function it_must_be_throw_an_exception_when_using_unit_and_currencies()
    {
        $this->shouldThrow('\InvalidArgumentException')->during(
            '__construct',
            [
                $code = 'my_code',
                $isScopable = false,
                $channelCode = null,
                $isLocalizable = false,
                $localeCodes = ['en_US', 'fr_FR'],
                $isMedia = true,
                $usesUnit = true,
                $usesCurrencies = true
            ]
        );
    }

    function it_generates_an_empty_header_string_for_non_supported_locales()
    {
        $this->beConstructedWith(
            $code = 'my_code',
            $isScopable = false,
            $channelCode = null,
            $isLocalizable = false,
            $localeCodes = ['en_US', 'fr_FR'],
            $isMedia = true,
            $usesUnit = false,
            $usesCurrencies = false,
            $channelCurrencies = null,
            $allCurrencies = null,
            $isLocaleSpecific = true,
            $specificToLocales = ['de_DE']
        );

        $this->generateHeaderStrings()->shouldReturn([]);
    }

    function it_generates_a_header_string_if_locales_supported()
    {
        $this->beConstructedWith(
            $code = 'my_code',
            $isScopable = false,
            $channelCode = null,
            $isLocalizable = false,
            $localeCodes = ['en_US', 'fr_FR'],
            $isMedia = true,
            $usesUnit = false,
            $usesCurrencies = false,
            $channelCurrencies = null,
            $allCurrencies = null,
            $isLocaleSpecific = true,
            $specificToLocales = ['en_US']
        );

        $this->generateHeaderStrings()->shouldReturn(['my_code']);
    }

    function it_generates_header_strings_for_supported_locales_when_localizable()
    {
        $this->beConstructedWith(
            $code = 'my_code',
            $isScopable = false,
            $channelCode = null,
            $isLocalizable = true,
            $localeCodes = ['en_US', 'fr_FR', 'fr_BE'],
            $isMedia = true,
            $usesUnit = false,
            $usesCurrencies = false,
            $channelCurrencies = null,
            $allCurrencies = null,
            $isLocaleSpecific = true,
            $specificToLocales = ['en_US', 'de_DE', 'fr_BE']
        );

        $this->generateHeaderStrings()->shouldReturn([
            'my_code-en_US',
            'my_code-fr_BE'
        ]);
    }

    function it_generates_a_header_string_if_locales_supported_with_unit()
    {
        $this->beConstructedWith(
            $code = 'my_code',
            $isScopable = false,
            $channelCode = null,
            $isLocalizable = false,
            $localeCodes = ['en_US', 'fr_FR'],
            $isMedia = true,
            $usesUnit = true,
            $usesCurrencies = false,
            $channelCurrencies = null,
            $allCurrencies = null,
            $isLocaleSpecific = true,
            $specificToLocales = ['en_US']
        );

        $this->generateHeaderStrings()->shouldReturn([
            'my_code',
            'my_code-unit'
        ]);
    }

    function it_generates_a_header_string_if_locales_supported_with_currencies()
    {
        $this->beConstructedWith(
            $code = 'my_code',
            $isScopable = false,
            $channelCode = null,
            $isLocalizable = false,
            $localeCodes = ['en_US', 'fr_FR'],
            $isMedia = true,
            $usesUnit = false,
            $usesCurrencies = true,
            $channelCurrencies = ['USD', 'EUR'],
            $allCurrencies = ['USD', 'EUR', 'GBP'],
            $isLocaleSpecific = true,
            $specificToLocales = ['en_US']
        );

        $this->generateHeaderStrings()->shouldReturn([
            'my_code-USD',
            'my_code-EUR',
            'my_code-GBP'
        ]);
    }

    function it_generates_a_header_string_for_non_scopable_non_localizable()
    {
        $this->beConstructedWith('my_code');

        $this->generateHeaderStrings()->shouldReturn(['my_code']);
    }

    function it_generates_a_header_string_for_non_scopable_non_localizable_with_unit()
    {
        $this->beConstructedWith(
            $code = 'my_code',
            $isScopable = false,
            $channelCode = null,
            $isLocalizable = false,
            $localeCodes = ['en_US', 'fr_FR'],
            $isMedia = true,
            $usesUnit = true
        );

        $this->generateHeaderStrings()->shouldReturn([
            'my_code',
            'my_code-unit'
        ]);
    }

    function it_generates_a_header_string_for_non_scopable_non_localizable_with_currencies()
    {
        $this->beConstructedWith(
            $code = 'my_code',
            $isScopable = false,
            $channelCode = null,
            $isLocalizable = false,
            $localeCodes = ['en_US', 'fr_FR'],
            $isMedia = false,
            $usesUnit = false,
            $usesCurrencies = true,
            $channelCurrencies = ['USD', 'EUR'],
            $allCurrencies = ['USD', 'EUR', 'GBP']
        );

        $this->generateHeaderStrings()->shouldReturn([
            'my_code-USD',
            'my_code-EUR',
            'my_code-GBP'
        ]);
    }

    function it_generates_a_header_string_for_scopable_non_localizable()
    {
        $this->beConstructedWith(
            $code = 'my_code',
            $isScopable = true,
            $channelCode = 'ecommerce'
        );

        $this->generateHeaderStrings()->shouldReturn(['my_code-ecommerce']);
    }

    function it_generates_a_header_string_for_scopable_non_localizable_with_unit()
    {
        $this->beConstructedWith(
            $code = 'my_code',
            $isScopable = true,
            $channelCode = 'ecommerce',
            $isLocalizable = false,
            $localeCodes = ['en_US', 'fr_FR'],
            $isMedia = false,
            $usesUnit = true
        );

        $this->generateHeaderStrings()->shouldReturn([
            'my_code-ecommerce',
            'my_code-ecommerce-unit'
        ]);
    }

    function it_generates_a_header_string_for_scopable_non_localizable_with_currencies()
    {
        $this->beConstructedWith(
            $code = 'my_code',
            $isScopable = true,
            $channelCode = 'ecommerce',
            $isLocalizable = false,
            $localeCodes = ['en_US', 'fr_FR'],
            $isMedia = false,
            $usesUnit = false,
            $usesCurrencies = true,
            $channelCurrencies = ['USD', 'EUR'],
            $allCurrencies = ['USD', 'EUR', 'GBP']
        );

        $this->generateHeaderStrings()->shouldReturn([
            'my_code-ecommerce-USD',
            'my_code-ecommerce-EUR'
        ]);
    }

    function it_generates_headers_string_for_non_scopable_localizable()
    {
        $this->beConstructedWith(
            $code = 'my_code',
            $isScopable = false,
            $channelCode = null,
            $isLocalizable = true,
            $localeCodes = ['en_US', 'fr_FR']
        );

        $this->generateHeaderStrings()->shouldReturn([
            'my_code-en_US',
            'my_code-fr_FR'
        ]);
    }

    function it_generates_headers_string_for_non_scopable_localizable_with_unit()
    {
        $this->beConstructedWith(
            $code = 'my_code',
            $isScopable = false,
            $channelCode = null,
            $isLocalizable = true,
            $localeCodes = ['en_US', 'fr_FR'],
            $isMedia = true,
            $usesUnit = true
        );

        $this->generateHeaderStrings()->shouldReturn([
            'my_code-en_US',
            'my_code-en_US-unit',
            'my_code-fr_FR',
            'my_code-fr_FR-unit'
        ]);
    }

    function it_generates_headers_string_for_non_scopable_localizable_with_currencies()
    {
        $this->beConstructedWith(
            $code = 'my_code',
            $isScopable = false,
            $channelCode = null,
            $isLocalizable = true,
            $localeCodes = ['en_US', 'fr_FR'],
            $isMedia = false,
            $usesUnit = false,
            $usesCurrencies = true,
            $channelCurrencies = ['USD', 'EUR'],
            $allCurrencies = ['USD', 'EUR', 'GBP']
        );

        $this->generateHeaderStrings()->shouldReturn([
            'my_code-en_US-USD',
            'my_code-en_US-EUR',
            'my_code-en_US-GBP',
            'my_code-fr_FR-USD',
            'my_code-fr_FR-EUR',
            'my_code-fr_FR-GBP'
        ]);
    }

    function it_generates_headers_string_for_scopable_localizable()
    {
        $this->beConstructedWith(
            $code = 'my_code',
            $isScopable = true,
            $channelCode = 'ecommerce',
            $isLocalizable = true,
            $localeCodes = ['en_US', 'fr_FR']
        );

        $this->generateHeaderStrings()->shouldReturn([
            'my_code-en_US-ecommerce',
            'my_code-fr_FR-ecommerce'
        ]);
    }

    function it_generates_headers_string_for_scopable_localizable_with_unit()
    {
        $this->beConstructedWith(
            $code = 'my_code',
            $isScopable = true,
            $channelCode = 'ecommerce',
            $isLocalizable = true,
            $localeCodes = ['en_US', 'fr_FR'],
            $isMedia = true,
            $usesUnit = true
        );

        $this->generateHeaderStrings()->shouldReturn([
            'my_code-en_US-ecommerce',
            'my_code-en_US-ecommerce-unit',
            'my_code-fr_FR-ecommerce',
            'my_code-fr_FR-ecommerce-unit'
        ]);
    }

    function it_generates_headers_string_for_scopable_localizable_with_currencies()
    {
        $this->beConstructedWith(
            $code = 'my_code',
            $isScopable = true,
            $channelCode = 'ecommerce',
            $isLocalizable = true,
            $localeCodes = ['en_US', 'fr_FR'],
            $isMedia = true,
            $usesUnit = false,
            $usesCurrencies = true,
            $channelCurrencies = ['USD', 'EUR'],
            $allCurrencies = ['USD', 'EUR', 'GBP']
        );

        $this->generateHeaderStrings()->shouldReturn([
            'my_code-en_US-ecommerce-USD',
            'my_code-en_US-ecommerce-EUR',
            'my_code-fr_FR-ecommerce-USD',
            'my_code-fr_FR-ecommerce-EUR'
        ]);
    }
}
