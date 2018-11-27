<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\File\Csv;

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
 * Write product data into a csv file on the local filesystem
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
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

    public function __construct(
        ArrayConverterInterface $arrayConverter,
        BufferFactory $bufferFactory,
        FlatItemBufferFlusher $flusher,
        AttributeRepositoryInterface $attributeRepository,
        FileExporterPathGeneratorInterface $fileExporterPath,
        GenerateFlatHeadersFromFamilyCodesInterface $generateHeadersFromFamilyCodes,
        GenerateFlatHeadersFromAttributeCodesInterface $generateHeadersFromAttributeCodes,
        array $mediaAttributeTypes,
        string $jobParamFilePath = self::DEFAULT_FILE_PATH
    ) {
        parent::__construct(
            $arrayConverter,
            $bufferFactory,
            $flusher,
            $attributeRepository,
            $fileExporterPath,
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

        parent::initialize();
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        foreach ($items as $item) {
            if (isset($item['family']) && !in_array($item['family'], $this->familyCodes)) {
                $this->familyCodes[] = $item['family'];
            }
        }

        parent::write($items);
    }

    /**
     * {@inheritdoc}
     */
    public function flush()
    {
        $parameters = $this->stepExecution->getJobParameters();

        if ($parameters->has('withHeader') && true === $parameters->get('withHeader')) {
            $additionalHeaders = $this->getAdditionalHeaders($parameters);
            $this->flatRowBuffer->addToHeaders($additionalHeaders);
        }

        parent::flush();
    }

    /**
     * Return additional headers, based on the requested attributes if any,
     * and from the families definition
     */
    protected function getAdditionalHeaders(JobParameters $parameters): array
    {
        $filters = $parameters->get('filters');

        $localeCodes = isset($filters['structure']['locales']) ? $filters['structure']['locales'] : [$parameters->get('locale')];
        $channelCode = isset($filters['structure']['scope']) ? $filters['structure']['scope'] : $parameters->get('scope');

        $attributeCodes = [];

        if (isset($filters['structure']['attributes'])
            && !empty($filters['structure']['attributes'])) {
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
    protected function getItemIdentifier(array $product)
    {
        return $product['identifier'];
    }
}
