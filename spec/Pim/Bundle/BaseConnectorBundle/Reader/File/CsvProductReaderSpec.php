<?php

namespace spec\Pim\Bundle\BaseConnectorBundle\Reader\File;

use Doctrine\ORM\EntityManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\ChannelRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\CurrencyRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface;
use Pim\Component\Connector\ArrayConverter\Flat\Product\AttributeColumnInfoExtractor;
use Prophecy\Argument;

class CsvProductReaderSpec extends ObjectBehavior
{
    function let(
        EntityManager $em,
        AttributeColumnInfoExtractor $fieldExtractor,
        AttributeRepositoryInterface $attributeRepository,
        ChannelRepositoryInterface $channelRepository,
        LocaleRepositoryInterface $localeRepository,
        CurrencyRepositoryInterface $currencyRepository
    ) {
        $em->getRepository('Pim\Bundle\CatalogBundle\Entity\Attribute')->willReturn($attributeRepository);
        $em->getRepository('Pim\Bundle\CatalogBundle\Entity\Channel')->willReturn($channelRepository);
        $em->getRepository('Pim\Bundle\CatalogBundle\Entity\Locale')->willReturn($localeRepository);
        $em->getRepository('Pim\Bundle\CatalogBundle\Entity\Currency')->willReturn($currencyRepository);

        $attributeRepository->findMediaAttributeCodes()->willReturn(['view', 'manual']);

        $this->beConstructedWith(
            $em,
            $fieldExtractor,
            'Pim\Bundle\CatalogBundle\Entity\Attribute',
            'Pim\Bundle\CatalogBundle\Entity\Channel',
            'Pim\Bundle\CatalogBundle\Entity\Locale',
            'Pim\Bundle\CatalogBundle\Entity\Currency'
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\BaseConnectorBundle\Reader\File\CsvProductReader');
    }

    function it_is_a_csv_reader()
    {
        $this->shouldHaveType('Pim\Bundle\BaseConnectorBundle\Reader\File\CsvReader');
    }

    function it_transforms_media_paths_to_absolute_paths(
        $channelRepository,
        $localeRepository,
        $currencyRepository,
        $fieldExtractor
    ) {
        $this->setFilePath(
            __DIR__ . '/../../../../../../features/Context/fixtures/with_media.csv'
        );

        $channelRepository->getChannelCodes()->willReturn([]);
        $localeRepository->getActivatedLocaleCodes()->willReturn(['IT']);
        $currencyRepository->getActivatedCurrencyCodes()->willReturn([]);

        $fieldInfo = ['locale_code' => null, 'scope_code' => null, 'price_currency' => null];
        $fieldExtractor->extractColumnInfo('sku')->willReturn($fieldInfo);
        $fieldExtractor->extractColumnInfo('name')->willReturn($fieldInfo);
        $fieldExtractor->extractColumnInfo('view')->willReturn($fieldInfo);
        $fieldExtractor->extractColumnInfo('manual-fr_FR')->willReturn($fieldInfo);

        $this->read()
            ->shouldReturn([
                'sku'          => 'SKU-001',
                'name'         => 'door',
                'view'         => __DIR__ . '/../../../../../../features/Context/fixtures/sku-001.jpg',
                'manual-fr_FR' => __DIR__ . '/../../../../../../features/Context/fixtures/sku-001.txt',
            ])
        ;
    }

    function it_checks_localizable_attributes_in_the_header(
        $channelRepository,
        $localeRepository,
        $currencyRepository,
        $fieldExtractor
    ) {
        $this->setFilePath(
            __DIR__ . '/../../../../../../features/Context/fixtures/invalid_import_header.csv'
        );

        $channelRepository->getChannelCodes()->willReturn(['ecommerce']);
        $localeRepository->getActivatedLocaleCodes()->willReturn(['fr']);
        $currencyRepository->getActivatedCurrencyCodes()->willReturn(['EUR']);

        $fieldExtractor->extractColumnInfo('description-wronglocale-ecommerce')->willReturn(
            ['locale_code' => 'wronglocale', 'scope_code' => 'ecommerce', 'price_currency' => null]
        );
        $fieldExtractor->extractColumnInfo(Argument::any())->willReturn(
            ['locale_code' => null, 'scope_code' => null, 'price_currency' => null]
        );

        $this->shouldThrow(new \LogicException("Locale wronglocale does not exist."))->during('read');
    }

    function it_checks_scopable_attributes_in_the_header(
        $channelRepository,
        $localeRepository,
        $currencyRepository,
        $fieldExtractor
    ) {
        $this->setFilePath(
            __DIR__ . '/../../../../../../features/Context/fixtures/invalid_import_header.csv'
        );

        $channelRepository->getChannelCodes()->willReturn(['ecommerce']);
        $localeRepository->getActivatedLocaleCodes()->willReturn(['fr']);
        $currencyRepository->getActivatedCurrencyCodes()->willReturn(['EUR']);

        $fieldExtractor->extractColumnInfo('description-fr_FR-wrongscope')->willReturn(
            ['locale_code' => 'fr', 'scope_code' => 'wrongscope', 'price_currency' => null]
        );
        $fieldExtractor->extractColumnInfo(Argument::any())->willReturn(
            ['locale_code' => null, 'scope_code' => null, 'price_currency' => null]
        );

        $this->shouldThrow(new \LogicException("Channel wrongscope does not exist."))->during('read');
    }

    function it_checks_currencies_attributes_in_the_header(
        $channelRepository,
        $localeRepository,
        $currencyRepository,
        $fieldExtractor
    ) {
        $this->setFilePath(
            __DIR__ . '/../../../../../../features/Context/fixtures/invalid_import_header.csv'
        );

        $channelRepository->getChannelCodes()->willReturn(['ecommerce']);
        $localeRepository->getActivatedLocaleCodes()->willReturn(['fr']);
        $currencyRepository->getActivatedCurrencyCodes()->willReturn(['EUR']);

        $fieldExtractor->extractColumnInfo('price-wrongcurrency')->willReturn(
            ['locale_code' => null, 'scope_code' => null, 'price_currency' => 'wrongcurrency']
        );
        $fieldExtractor->extractColumnInfo(Argument::any())->willReturn(
            ['locale_code' => null, 'scope_code' => null, 'price_currency' => null]
        );

        $this->shouldThrow(new \LogicException("Currency wrongcurrency does not exist."))->during('read');
    }
}
