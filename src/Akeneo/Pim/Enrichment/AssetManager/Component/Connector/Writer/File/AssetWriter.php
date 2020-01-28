<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Enrichment\AssetManager\Component\Connector\Writer\File;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Attribute\FindAttributesIndexedByIdentifierInterface;
use Akeneo\AssetManager\Domain\Query\Channel\FindActivatedLocalesPerChannelsInterface;
use Akeneo\Tool\Component\Batch\Item\FlushableInterface;
use Akeneo\Tool\Component\Batch\Item\InitializableInterface;
use Akeneo\Tool\Component\Buffer\BufferFactory;
use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\Tool\Component\Connector\Writer\File\AbstractFileWriter;
use Akeneo\Tool\Component\Connector\Writer\File\ArchivableWriterInterface;
use Akeneo\Tool\Component\Connector\Writer\File\FlatItemBuffer;
use Akeneo\Tool\Component\Connector\Writer\File\FlatItemBufferFlusher;

class AssetWriter extends AbstractFileWriter implements InitializableInterface, FlushableInterface, ArchivableWriterInterface
{
    /** @var ArrayConverterInterface */
    private $arrayConverter;

    /** @var BufferFactory */
    private $bufferFactory;

    /** @var FlatItemBufferFlusher */
    private $flusher;

    /** @var FindAttributesIndexedByIdentifierInterface */
    private $findAttributesIndexedByIdentifier;

    /** @var FindActivatedLocalesPerChannelsInterface */
    private $findActivatedLocalesPerChannels;

    /** @var FlatItemBuffer */
    protected $flatRowBuffer;

    /** @var array */
    private $writtenFiles = [];

    public function __construct(
        ArrayConverterInterface $arrayConverter,
        BufferFactory $bufferFactory,
        FlatItemBufferFlusher $flusher,
        FindAttributesIndexedByIdentifierInterface $findAttributesIndexedByIdentifier,
        FindActivatedLocalesPerChannelsInterface $findActivatedLocalesPerChannels
    ) {
        $this->arrayConverter = $arrayConverter;
        $this->bufferFactory = $bufferFactory;
        $this->flusher = $flusher;
        $this->findAttributesIndexedByIdentifier = $findAttributesIndexedByIdentifier;
        $this->findActivatedLocalesPerChannels = $findActivatedLocalesPerChannels;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    public function initialize()
    {
        if (null === $this->flatRowBuffer) {
            $this->flatRowBuffer = $this->bufferFactory->create();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getWrittenFiles()
    {
        return $this->writtenFiles;
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        $exportDirectory = dirname($this->getPath());
        if (!is_dir($exportDirectory)) {
            $this->localFs->mkdir($exportDirectory);
        }

        $flatItems = [];
        foreach ($items as $item) {
            $flatItems[] = $this->arrayConverter->convert($item);
        }

        $parameters = $this->stepExecution->getJobParameters();
        $options = [];
        $options['withHeader'] = $parameters->get('withHeader');
        $this->flatRowBuffer->write($flatItems, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function flush()
    {
        if (true === $this->stepExecution->getJobParameters()->get('withHeader')) {
            $this->flatRowBuffer->addToHeaders($this->getAdditionalHeaders());
        }
        $this->flusher->setStepExecution($this->stepExecution);

        $parameters = $this->stepExecution->getJobParameters();
        $writerOptions = [
            'type' => 'csv',
            'fieldDelimiter' => $parameters->get('delimiter'),
            'fieldEnclosure' => $parameters->get('enclosure'),
            'shouldAddBOM' => false,
        ];

        $writtenFiles = $this->flusher->flush(
            $this->flatRowBuffer,
            $writerOptions,
            $this->getPath(),
            ($parameters->has('linesPerFile') ? $parameters->get('linesPerFile') : -1)
        );

        foreach ($writtenFiles as $writtenFile) {
            $this->writtenFiles[$writtenFile] = basename($writtenFile);
        }
    }

    private function getAdditionalHeaders(): array
    {
        $localesPerChannel = $this->findActivatedLocalesPerChannels->findAll();
        $activeChannelCodes = array_keys($localesPerChannel);
        $activeLocaleCodes = array_unique(array_merge(...array_values($localesPerChannel)));

        $assetFamilyIdentifier = $this->stepExecution->getJobParameters()->get('asset_family_identifier');
        $attributes = $this->findAttributesIndexedByIdentifier->find(
            AssetFamilyIdentifier::fromString($assetFamilyIdentifier)
        );

        $headers = [];

        foreach ($attributes as $attribute) {
            if ($attribute->hasValuePerChannel() && $attribute->hasValuePerLocale()) {
                foreach ($localesPerChannel as $channelCode => $localeCodes) {
                    foreach ($localeCodes as $localeCode) {
                        $headers[] = sprintf(
                            '%s-%s-%s',
                            $attribute->getCode()->__toString(),
                            $localeCode,
                            $channelCode
                        );
                    }
                }
            } elseif ($attribute->hasValuePerChannel()) {
                foreach ($activeChannelCodes as $channelCode) {
                    $headers[] = sprintf('%s-%s', $attribute->getCode()->__toString(), $channelCode);
                }
            } elseif ($attribute->hasValuePerLocale()) {
                foreach ($activeLocaleCodes as $localeCode) {
                    $headers[] = sprintf('%s-%s', $attribute->getCode()->__toString(), $localeCode);
                }
            } else {
                $headers[] = $attribute->getCode()->__toString();
            }
        }

        return $headers;
    }
}
