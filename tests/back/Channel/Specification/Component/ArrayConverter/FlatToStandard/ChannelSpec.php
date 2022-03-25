<?php

namespace Specification\Akeneo\Channel\Component\ArrayConverter\FlatToStandard;

use Akeneo\Channel\Component\Model\Locale;
use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Tool\Component\Connector\ArrayConverter\FieldsRequirementChecker;

class ChannelSpec extends ObjectBehavior
{
    function let(FieldsRequirementChecker $fieldChecker, IdentifiableObjectRepositoryInterface $localeRepository)
    {
        $this->beConstructedWith($fieldChecker, $localeRepository);
    }

    function it_is_a_standard_array_converter()
    {
        $this->shouldImplement(
            ArrayConverterInterface::class
        );
    }

    function it_converts_an_item_to_standard_format($localeRepository)
    {
        $localeEn = (new Locale())->setCode('en_US');
        $localeFr = (new Locale())->setCode('fr_FR');
        
        $localeRepository->findOneByIdentifier('en_US')->willReturn($localeEn);
        $localeRepository->findOneByIdentifier('fr_FR')->willReturn($localeFr);
        
        $item = [
            'code'        => 'ecommerce',
            'label-fr_FR' => 'Ecommerce',
            'label-en_US' => 'Ecommerce',
            'locales'     => 'en_US,fr_FR',
            'currencies'  => 'EUR,USD',
            'tree'        => 'master_catalog',
            'conversion_units' => 'weight: GRAM,maximum_scan_size:KILOMETER, display_diagonal:DEKAMETER, viewing_area: DEKAMETER'
        ];

        $result = [
            'labels'           => [
                'fr_FR' => 'Ecommerce',
                'en_US' => 'Ecommerce',
            ],
            'code'             => 'ecommerce',
            'locales'          => ['en_US', 'fr_FR'],
            'currencies'       => ['EUR', 'USD'],
            'category_tree'    => 'master_catalog',
            'conversion_units' => [
                'weight'            => 'GRAM',
                'maximum_scan_size' => 'KILOMETER',
                'display_diagonal'  => 'DEKAMETER',
                'viewing_area'      => 'DEKAMETER'
            ]
        ];

        $this->convert($item)->shouldReturn($result);
    }

    public function it_converts_labels_with_the_right_locale_code_letter_case($localeRepository): void
    {
        $localeEn = (new Locale())->setCode('en_US');

        $localeRepository->findOneByIdentifier('en_uS')->willReturn($localeEn);
        $localeRepository->findOneByIdentifier('xx_XX')->willReturn(null);

        $item = [
            'code'        => 'ecommerce',
            'label-en_uS' => 'Ecommerce',
            'label-xx_XX' => 'Unknown locale',
            'locales'     => 'en_US',
            'currencies'  => 'USD',
            'tree'        => 'master_catalog',
        ];

        $expectedResult = [
            'labels'           => [
                'en_US' => 'Ecommerce',
                'xx_XX' => 'Unknown locale',
            ],
            'code'             => 'ecommerce',
            'locales'          => ['en_US'],
            'currencies'       => ['USD'],
            'category_tree'    => 'master_catalog',
        ];

        $this->convert($item)->shouldReturn($expectedResult);
    }

    function it_converts_empty_conversion_units()
    {
        $this->convert(['conversion_units' => ''])->shouldReturn(['labels' => [], 'conversion_units' => []]);
    }
}
