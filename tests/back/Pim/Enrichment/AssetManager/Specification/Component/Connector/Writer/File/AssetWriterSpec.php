<?php

namespace Specification\Akeneo\Pim\Enrichment\AssetManager\Component\Connector\Writer\File;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Query\Attribute\FindAttributesIndexedByIdentifierInterface;
use Akeneo\AssetManager\Domain\Query\Channel\FindActivatedLocalesPerChannelsInterface;
use Akeneo\Pim\Enrichment\AssetManager\Component\Connector\Writer\File\AssetWriter;
use Akeneo\Tool\Component\Batch\Item\FlushableInterface;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Buffer\BufferFactory;
use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\Tool\Component\Connector\Writer\File\AbstractFileWriter;
use Akeneo\Tool\Component\Connector\Writer\File\ArchivableWriterInterface;
use Akeneo\Tool\Component\Connector\Writer\File\FlatItemBuffer;
use Akeneo\Tool\Component\Connector\Writer\File\FlatItemBufferFlusher;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Filesystem\Filesystem;

class AssetWriterSpec extends ObjectBehavior
{
    /** @var Filesystem */
    private $filesystem;

    /** @var string */
    private $directory;

    function let(
        ArrayConverterInterface $arrayConverter,
        BufferFactory $bufferFactory,
        FlatItemBufferFlusher $flusher,
        FindAttributesIndexedByIdentifierInterface $findAttributesIndexedByIdentifier,
        FindActivatedLocalesPerChannelsInterface $findActivatedLocalesPerChannels,
        FlatItemBuffer $flatRowBuffer,
        StepExecution $stepExecution,
        JobParameters $jobParameters
    ) {
        $this->directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'spec' . DIRECTORY_SEPARATOR;
        $this->filesystem = new Filesystem();
        $this->filesystem->mkdir($this->directory);

        $this->beConstructedWith(
            $arrayConverter,
            $bufferFactory,
            $flusher,
            $findAttributesIndexedByIdentifier,
            $findActivatedLocalesPerChannels
        );

        $jobParameters->get('filePath')->willReturn($this->directory . 'export_assets.csv');
        $stepExecution->getJobParameters()->willReturn($jobParameters);

        $this->setStepExecution($stepExecution);
        $bufferFactory->create()->willReturn($flatRowBuffer);
        $this->initialize();
    }

    function letGo()
    {
        $this->filesystem->remove($this->directory);
    }

    function it_is_a_file_writer()
    {
        $this->shouldBeAnInstanceOf(AbstractFileWriter::class);
        $this->shouldImplement(ItemWriterInterface::class);
    }

    function it_is_an_asset_file_writer()
    {
        $this->shouldImplement(FlushableInterface::class);
        $this->shouldImplement(ArchivableWriterInterface::class);
        $this->shouldBeAnInstanceOf(AssetWriter::class);
    }

    function it_writes_items_to_the_file_buffer(
        ArrayConverterInterface $arrayConverter,
        FlatItemBuffer $flatRowBuffer,
        JobParameters $jobParameters
    ) {
        $jobParameters->get('withHeader')->willReturn(true);

        $normalizedAssets = [
            [
                'identifier' => 'test_identifier_1',
                'code' => 'asset_code_1',
                'assetFamilyIdentifier' => 'packshot',
                'values' => ['normalized_values_1'],
            ],
            [
                'identifier' => 'test_identifier_2',
                'code' => 'asset_code_2',
                'assetFamilyIdentifier' => 'packshot',
                'values' => ['normalized_values_2'],
            ]
        ];
        $arrayConverter->convert($normalizedAssets[0])->willReturn(['converted_asset_1']);
        $arrayConverter->convert($normalizedAssets[1])->willReturn(['converted_asset_2']);

        $flatRowBuffer->write([['converted_asset_1'], ['converted_asset_2']], ['withHeader' => true])->shouldBeCalled();

        $this->write($normalizedAssets);
    }

    function it_adds_missing_headers_and_flushes_buffer_to_the_target_file(
        FlatItemBufferFlusher $flusher,
        FlatItemBuffer $flatRowBuffer,
        FindAttributesIndexedByIdentifierInterface $findAttributesIndexedByIdentifier,
        FindActivatedLocalesPerChannelsInterface $findActivatedLocalesPerChannels,
        StepExecution $stepExecution,
        JobParameters $jobParameters,
        AbstractAttribute $scopableLocalizableAttribute,
        AbstractAttribute $scopableAttribute,
        AbstractAttribute $localizableAttribute,
        AbstractAttribute $nonScopableNonLocalizableAttribute
    ) {
        $jobParameters->get('withHeader')->willReturn(true);
        $jobParameters->get('asset_family_identifier')->willReturn('packshot');
        $jobParameters->get('delimiter')->willReturn(';');
        $jobParameters->get('enclosure')->willReturn('"');
        $jobParameters->has('linesPerFile')->willReturn(false);

        $findActivatedLocalesPerChannels->findAll()->willReturn(
            [
                'ecommerce' => ['en_US', 'fr_FR'],
                'mobile' => ['de_DE'],
            ]
        );

        $scopableLocalizableAttribute->getCode()->willReturn(AttributeCode::fromString('scopable_and_localizable'));
        $scopableLocalizableAttribute->hasValuePerChannel()->willReturn(true);
        $scopableLocalizableAttribute->hasValuePerLocale()->willReturn(true);

        $scopableAttribute->getCode()->willReturn(AttributeCode::fromString('scopable'));
        $scopableAttribute->hasValuePerChannel()->willReturn(true);
        $scopableAttribute->hasValuePerLocale()->willReturn(false);

        $localizableAttribute->getCode()->willReturn(AttributeCode::fromString('localizable'));
        $localizableAttribute->hasValuePerChannel()->willReturn(false);
        $localizableAttribute->hasValuePerLocale()->willReturn(true);

        $nonScopableNonLocalizableAttribute->getCode()->willReturn(AttributeCode::fromString('simple'));
        $nonScopableNonLocalizableAttribute->hasValuePerChannel()->willReturn(false);
        $nonScopableNonLocalizableAttribute->hasValuePerLocale()->willReturn(false);

        $findAttributesIndexedByIdentifier->find(AssetFamilyIdentifier::fromString('packshot'))->willReturn([
            $scopableLocalizableAttribute,
            $scopableAttribute,
            $localizableAttribute,
            $nonScopableNonLocalizableAttribute
        ]);

        $flatRowBuffer->addToHeaders([
           'scopable_and_localizable-ecommerce-en_US',
           'scopable_and_localizable-ecommerce-fr_FR',
           'scopable_and_localizable-mobile-de_DE',
           'scopable-ecommerce',
           'scopable-mobile',
           'localizable-en_US',
           'localizable-fr_FR',
           'localizable-de_DE',
           'simple',
        ])->shouldBeCalled();

        $flusher->setStepExecution($stepExecution)->shouldBeCalled();
        $flusher->flush(
            $flatRowBuffer,
            [
                'type' => 'csv',
                'fieldDelimiter' => ';',
                'fieldEnclosure' => '"',
                'shouldAddBOM' => false,
            ],
            $this->directory . 'export_assets.csv',
            -1
        )->shouldBeCalled()->willReturn([
            $this->directory . 'export_assets_1.csv',
            $this->directory . 'export_assets_2.csv',
        ]);

        $this->flush();

        $this->getWrittenFiles()->shouldReturn([
            $this->directory . 'export_assets_1.csv' => 'export_assets_1.csv',
            $this->directory . 'export_assets_2.csv' =>'export_assets_2.csv']
        );
    }
}
