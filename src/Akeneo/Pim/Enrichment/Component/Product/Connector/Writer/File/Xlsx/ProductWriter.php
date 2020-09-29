<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\File\Xlsx;

use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\FlatTranslatorInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\File\GenerateFlatHeadersFromAttributeCodesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\File\GenerateFlatHeadersFromFamilyCodesInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\Batch\Item\FlushableInterface;
use Akeneo\Tool\Component\Batch\Item\InitializableInterface;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\Buffer\BufferFactory;
use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\Tool\Component\Connector\Writer\File\AbstractItemMediaWriter;
use Akeneo\Tool\Component\Connector\Writer\File\ArchivableWriterInterface;
use Akeneo\Tool\Component\Connector\Writer\File\FileExporterPathGeneratorInterface;
use Akeneo\Tool\Component\Connector\Writer\File\FlatItemBufferFlusher;

/**
 * Write product data into a XLSX file on the local filesystem
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductWriter extends AbstractItemMediaWriter implements
    ItemWriterInterface,
    InitializableInterface,
    FlushableInterface,
    StepExecutionAwareInterface,
    ArchivableWriterInterface
{
    /** @var array */
    protected $familyCodes;

    /** @var GenerateFlatHeadersFromFamilyCodesInterface */
    protected $generateHeadersFromFamilyCodes;

    /** @var GenerateFlatHeadersFromAttributeCodesInterface */
    protected $generateHeadersFromAttributeCodes;

    private $hasItems;

    public function __construct(
        ArrayConverterInterface $arrayConverter,
        BufferFactory $bufferFactory,
        FlatItemBufferFlusher $flusher,
        AttributeRepositoryInterface $attributeRepository,
        FileExporterPathGeneratorInterface $fileExporterPath,
        GenerateFlatHeadersFromFamilyCodesInterface $generateHeadersFromFamilyCodes,
        GenerateFlatHeadersFromAttributeCodesInterface $generateHeadersFromAttributeCodes,
        FlatTranslatorInterface $flatTranslator,
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
            $mediaAttributeTypes,
            $jobParamFilePath
        );

        $this->generateHeadersFromFamilyCodes = $generateHeadersFromFamilyCodes;
        $this->generateHeadersFromAttributeCodes = $generateHeadersFromAttributeCodes;
    }

    /**
     * {@inheritdoc}
     */
    public function initialize()
    {
        $this->familyCodes = [];
        $this->hasItems = false;

        parent::initialize();
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $items)
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
    protected function getWriterConfiguration()
    {
        return ['type' => 'xlsx'];
    }

    /**
     * {@inheritdoc}
     */
    protected function getItemIdentifier(array $product)
    {
        return $product['identifier'];
    }
}
