<?php

namespace spec\Pim\Bundle\BaseConnectorBundle\Reader\File;

use Doctrine\ORM\EntityManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\ChannelRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\CurrencyRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface;
use Pim\Bundle\TransformBundle\Builder\FieldNameBuilder;
use Prophecy\Argument;

class CsvProductReaderSpec extends ObjectBehavior
{
    function let(
        EntityManager $em,
        FieldNameBuilder $fieldNameBuilder,
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
            $fieldNameBuilder,
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
        $fieldNameBuilder
    ) {
        $this->setFilePath(
            __DIR__ . '/../../../../../../src/Pim/Bundle/BaseConnectorBundle/Tests/fixtures/with_media.csv'
        );

        $channelRepository->getChannelCodes()->willReturn([]);
        $localeRepository->getActivatedLocaleCodes()->willReturn(['IT']);
        $currencyRepository->getActivatedCurrencyCodes()->willReturn([]);

        $fieldInfo = ['locale_code' => null, 'scope_code' => null, 'price_currency' => null];
        $fieldNameBuilder->extractAttributeFieldNameInfos('sku')->willReturn($fieldInfo);
        $fieldNameBuilder->extractAttributeFieldNameInfos('name')->willReturn($fieldInfo);
        $fieldNameBuilder->extractAttributeFieldNameInfos('view')->willReturn($fieldInfo);
        $fieldNameBuilder->extractAttributeFieldNameInfos('manual-fr_FR')->willReturn($fieldInfo);

        $this->read()
            ->shouldReturn([
                'sku'          => 'SKU-001',
                'name'         => 'door',
                'view'         =>
                    __DIR__ . '/../../../../../../src/Pim/Bundle/BaseConnectorBundle/Tests/fixtures/sku-001.jpg',
                'manual-fr_FR' =>
                    __DIR__ . '/../../../../../../src/Pim/Bundle/BaseConnectorBundle/Tests/fixtures/sku-001.txt',
            ])
        ;
    }

    function it_checks_localizable_attributes_in_the_header(
        $channelRepository,
        $localeRepository,
        $currencyRepository,
        $fieldNameBuilder
    ) {
        $this->setFilePath(
            __DIR__ . '/../../../../../../src/Pim/Bundle/BaseConnectorBundle/Tests/fixtures/invalid_import_header.csv'
        );

        $channelRepository->getChannelCodes()->willReturn(['ecommerce']);
        $localeRepository->getActivatedLocaleCodes()->willReturn(['fr']);
        $currencyRepository->getActivatedCurrencyCodes()->willReturn(['EUR']);

        $fieldNameBuilder->extractAttributeFieldNameInfos('description-wronglocale-ecommerce')->willReturn(
            ['locale_code' => 'wronglocale', 'scope_code' => 'ecommerce', 'price_currency' => null]
        );
        $fieldNameBuilder->extractAttributeFieldNameInfos(Argument::any())->willReturn(
            ['locale_code' => null, 'scope_code' => null, 'price_currency' => null]
        );

        $this->shouldThrow(new \LogicException("Locale wronglocale does not exist."))->during('read');
    }

    function it_checks_scopable_attributes_in_the_header(
        $channelRepository,
        $localeRepository,
        $currencyRepository,
        $fieldNameBuilder
    ) {
        $this->setFilePath(
            __DIR__ . '/../../../../../../src/Pim/Bundle/BaseConnectorBundle/Tests/fixtures/invalid_import_header.csv'
        );

        $channelRepository->getChannelCodes()->willReturn(['ecommerce']);
        $localeRepository->getActivatedLocaleCodes()->willReturn(['fr']);
        $currencyRepository->getActivatedCurrencyCodes()->willReturn(['EUR']);

        $fieldNameBuilder->extractAttributeFieldNameInfos('description-fr_FR-wrongscope')->willReturn(
            ['locale_code' => 'fr', 'scope_code' => 'wrongscope', 'price_currency' => null]
        );
        $fieldNameBuilder->extractAttributeFieldNameInfos(Argument::any())->willReturn(
            ['locale_code' => null, 'scope_code' => null, 'price_currency' => null]
        );

        $this->shouldThrow(new \LogicException("Channel wrongscope does not exist."))->during('read');
    }

    function it_checks_currencies_attributes_in_the_header(
        $channelRepository,
        $localeRepository,
        $currencyRepository,
        $fieldNameBuilder
    ) {
        $this->setFilePath(
            __DIR__ . '/../../../../../../src/Pim/Bundle/BaseConnectorBundle/Tests/fixtures/invalid_import_header.csv'
        );

        $channelRepository->getChannelCodes()->willReturn(['ecommerce']);
        $localeRepository->getActivatedLocaleCodes()->willReturn(['fr']);
        $currencyRepository->getActivatedCurrencyCodes()->willReturn(['EUR']);

        $fieldNameBuilder->extractAttributeFieldNameInfos('price-wrongcurrency')->willReturn(
            ['locale_code' => null, 'scope_code' => null, 'price_currency' => 'wrongcurrency']
        );
        $fieldNameBuilder->extractAttributeFieldNameInfos(Argument::any())->willReturn(
            ['locale_code' => null, 'scope_code' => null, 'price_currency' => null]
        );

        $this->shouldThrow(new \LogicException("Currency wrongcurrency does not exist."))->during('read');
    }

}
