<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\File\Csv;

use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\FlatTranslatorInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\File\GenerateFlatHeadersFromAttributeCodesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\File\GenerateFlatHeadersFromFamilyCodesInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\Batch\Item\InitializableInterface;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Buffer\BufferFactory;
use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\Tool\Component\Connector\Writer\File\AbstractItemMediaWriter;
use Akeneo\Tool\Component\Connector\Writer\File\FileExporterPathGeneratorInterface;
use Akeneo\Tool\Component\Connector\Writer\File\FlatItemBufferFlusher;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;
use Akeneo\Tool\Component\FileStorage\Repository\FileInfoRepositoryInterface;

/**
 * Write product data into a csv file on the local filesystem
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductWriter extends AbstractItemMediaWriter implements ItemWriterInterface, InitializableInterface
{
    protected GenerateFlatHeadersFromFamilyCodesInterface $generateHeadersFromFamilyCodes;
    protected GenerateFlatHeadersFromAttributeCodesInterface $generateHeadersFromAttributeCodes;

    protected array $familyCodes = [];
    private bool $hasItems = false;

    public function __construct(
        ArrayConverterInterface $arrayConverter,
        BufferFactory $bufferFactory,
        FlatItemBufferFlusher $flusher,
        AttributeRepositoryInterface $attributeRepository,
        FileExporterPathGeneratorInterface $fileExporterPath,
        GenerateFlatHeadersFromFamilyCodesInterface $generateHeadersFromFamilyCodes,
        GenerateFlatHeadersFromAttributeCodesInterface $generateHeadersFromAttributeCodes,
        FlatTranslatorInterface $flatTranslator,
        FileInfoRepositoryInterface $fileInfoRepository,
        FilesystemProvider $filesystemProvider,
        array $mediaAttributeTypes,
        string $jobParamFilePath = self::DEFAULT_FILE_PATH
    ) {
        parent::__construct(
            $arrayConverter,
            $bufferFactory,
            $flusher,
            $attributeRepository,
            $fileExporterPath,
            $flatTranslator,
            $fileInfoRepository,
            $filesystemProvider,
            $mediaAttributeTypes,
            $jobParamFilePath
        );

        $this->generateHeadersFromFamilyCodes = $generateHeadersFromFamilyCodes;
        $this->generateHeadersFromAttributeCodes = $generateHeadersFromAttributeCodes;
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(): void
    {
        $this->familyCodes = [];
        $this->hasItems = false;

        parent::initialize();
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $items): void
    {
        $this->hasItems = true;
        foreach ($items as $item) {
            if (isset($item['family']) && !in_array($item['family'], $this->familyCodes)) {
                $this->familyCodes[] = $item['family'];
            }
        }

        parent::write($items);
    }

    /**
     * Return additional headers, based on the requested attributes if any,
     * and from the families definition
     */
    protected function getAdditionalHeaders(): array
    {
        $parameters = $this->stepExecution->getJobParameters();

        $filters = $parameters->get('filters');
        $localeCodes = isset($filters['structure']['locales']) ? $filters['structure']['locales'] : [$parameters->get('locale')];
        $channelCode = isset($filters['structure']['scope']) ? $filters['structure']['scope'] : $parameters->get('scope');

        $attributeCodes = [];

        if (isset($filters['structure']['attributes'])
            && !empty($filters['structure']['attributes'])
            && $this->hasItems === true) {
            $attributeCodes = $filters['structure']['attributes'];
        } elseif ($parameters->has('selected_properties')) {
            $attributeCodes = $parameters->get('selected_properties');
        }

        $headers = [];
        if (!empty($attributeCodes)) {
            $headers = ($this->generateHeadersFromAttributeCodes)($attributeCodes, $channelCode, $localeCodes);
        } elseif (!empty($this->familyCodes)) {
            $headers = ($this->generateHeadersFromFamilyCodes)($this->familyCodes, $channelCode, $localeCodes);
        }

        $withMedia = (!$parameters->has('with_media') || $parameters->has('with_media') && $parameters->get('with_media'));

        $headerStrings = [];
        foreach ($headers as $header) {
            if ($withMedia || !$header->isMedia()) {
                $headerStrings = array_merge(
                    $headerStrings,
                    $header->generateHeaderStrings()
                );
            }
        }

        return $headerStrings;
    }

    /**
     * {@inheritdoc}
     */
    protected function getWriterConfiguration(): array
    {
        $parameters = $this->stepExecution->getJobParameters();

        return [
            'type'           => 'csv',
            'fieldDelimiter' => $parameters->get('delimiter'),
            'fieldEnclosure' => $parameters->get('enclosure'),
            'shouldAddBOM'   => false,
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getItemIdentifier(array $product): string
    {
        return $product['identifier'];
    }
}
