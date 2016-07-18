<?php

namespace Pim\Component\Connector\Writer\File\Csv;

use Akeneo\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Buffer\BufferFactory;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Pim\Component\Connector\Writer\File\AbstractFileWriter;
use Pim\Component\Connector\Writer\File\ArchivableWriterInterface;
use Pim\Component\Connector\Writer\File\FileExporterPathGeneratorInterface;
use Pim\Component\Connector\Writer\File\FlatItemBuffer;
use Pim\Component\Connector\Writer\File\FlatItemBufferFlusher;
use Symfony\Component\Finder\Finder;

/**
 * Write product data into a csv file on the local filesystem
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductWriter extends AbstractFileWriter implements ItemWriterInterface, ArchivableWriterInterface
{
    /** @var ArrayConverterInterface */
    protected $arrayConverter;

    /** @var FlatItemBufferFlusher */
    protected $flusher;

    /** @var BufferFactory */
    protected $bufferFactory;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var FileExporterPathGeneratorInterface */
    protected $fileExporterPath;

    /** @var array */
    protected $mediaAttributeTypes;

    /** @var array */
    protected $writtenFiles = [];

    /** @var FlatItemBuffer */
    protected $flatRowBuffer = null;

    /**
     * @param ArrayConverterInterface            $arrayConverter
     * @param BufferFactory                      $bufferFactory
     * @param FlatItemBufferFlusher              $flusher
     * @param AttributeRepositoryInterface       $attributeRepository
     * @param FileExporterPathGeneratorInterface $fileExporterPath
     * @param array                              $mediaAttributeTypes
     */
    public function __construct(
        ArrayConverterInterface $arrayConverter,
        BufferFactory $bufferFactory,
        FlatItemBufferFlusher $flusher,
        AttributeRepositoryInterface $attributeRepository,
        FileExporterPathGeneratorInterface $fileExporterPath,
        array $mediaAttributeTypes
    ) {
        parent::__construct();

        $this->arrayConverter = $arrayConverter;
        $this->bufferFactory = $bufferFactory;
        $this->flusher = $flusher;
        $this->attributeRepository = $attributeRepository;
        $this->mediaAttributeTypes = $mediaAttributeTypes;
        $this->fileExporterPath = $fileExporterPath;
    }

    /**
     * {@inheritdoc}
     */
    public function initialize()
    {
        if (null === $this->flatRowBuffer) {
            $this->flatRowBuffer = $this->bufferFactory->create();
        }

        $exportDirectory = dirname($this->getPath());
        if (!is_dir($exportDirectory)) {
            $this->localFs->mkdir($exportDirectory);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $products)
    {
        $parameters = $this->stepExecution->getJobParameters();

        $flatItems = [];
        foreach ($products as $product) {
            if ($parameters->has('with_media') && $parameters->get('with_media')) {
                $product = $this->archiveMedia($product, $parameters);
            }

            $flatItems[] = $this->arrayConverter->convert($product, [
                'decimal_separator' => $parameters->get('decimalSeparator'),
                'date_format'       => $parameters->get('dateFormat'),
            ]);
        }

        $options = [];
        $options['withHeader'] = $parameters->get('withHeader');
        $this->flatRowBuffer->write($flatItems, $options);
    }

    /**
     * Stock media for archiver and change media path
     *
     * @param array         $product
     * @param JobParameters $parameters
     *
     * @return array
     */
    protected function archiveMedia(array $product, JobParameters $parameters)
    {
        $attributeTypes = $this->attributeRepository->getAttributeTypeByCodes(array_keys($product['values']));
        $mediaAttributeTypes = array_filter($attributeTypes, function ($attributeCode) {
            return in_array($attributeCode, $this->mediaAttributeTypes);
        });

        $tmpDirectory = dirname($this->getPath()) . DIRECTORY_SEPARATOR;

        $identifierCode = $this->attributeRepository->getIdentifierCode();
        $identifier = current($product['values'][$identifierCode])['data'];
        $finder = new Finder();

        foreach ($mediaAttributeTypes as $attributeCode => $attributeType) {
            foreach ($product['values'][$attributeCode] as $index => $item) {
                if (null !== $item['data']) {
                    // TODO: change this
                    $exportDirectory = str_replace($item['data']['filePath'], '', $this->fileExporterPath->generate($item, [
                        'identifier' => $identifier,
                        'code'       => $attributeCode,
                    ]));

                    $files = iterator_to_array($finder->files()->in($tmpDirectory . $exportDirectory));
                    if (!empty($files)) {
                        $path = $exportDirectory . current($files)->getFilename();
                        $this->writtenFiles[$tmpDirectory . $path] = $path;
                        $product['values'][$attributeCode][$index]['data']['filePath'] = $path;
                    }
                }
            }
        }

        return $product;
    }

    /**
     * Flush items into a csv file
     */
    public function flush()
    {
        $this->flusher->setStepExecution($this->stepExecution);

        $parameters = $this->stepExecution->getJobParameters();
        $writerOptions = [
            'type'           => 'csv',
            'fieldDelimiter' => $parameters->get('delimiter'),
            'fieldEnclosure' => $parameters->get('enclosure'),
            'shouldAddBOM'   => false,
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

    /**
     * {@inheritdoc}
     */
    public function getWrittenFiles()
    {
        return $this->writtenFiles;
    }
}
