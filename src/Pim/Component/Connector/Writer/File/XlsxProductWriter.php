<?php

namespace Pim\Component\Connector\Writer\File;

use Akeneo\Component\Batch\Item\AbstractConfigurableStepElement;
use Akeneo\Component\Batch\Item\ItemWriterInterface;
use Box\Spout\Common\Type;
use Box\Spout\Writer\WriterFactory;
use Pim\Component\Connector\Writer\File\Product\FlatRowBuffer;
use Pim\Component\Connector\Writer\File\Product\MediaCopier;

/**
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class XlsxProductWriter extends AbstractFileWriter implements ItemWriterInterface
{
    /** @var bool */
    protected $withHeader;

    /** @var FlatRowBuffer */
    private $flatRowBuffer;

    /** @var MediaCopier */
    private $mediaCopier;

    public function __construct(
        FilePathResolverInterface $filePathResolver,
        FlatRowBuffer $flatRowBuffer,
        MediaCopier $mediaCopier
    ) {
        parent::__construct($filePathResolver);

        $this->flatRowBuffer = $flatRowBuffer;
        $this->mediaCopier = $mediaCopier;
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

        $this->flatRowBuffer->write($items, $this->isWithHeader());
        $this->mediaCopier->copy($items, $exportDirectory);

        foreach ($this->mediaCopier->getErrors() as $error) {
            $this->stepExecution->addWarning(
                $this->getName(),
                $error['message'],
                [],
                $error['medium']
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function flush()
    {
        $writer = WriterFactory::create(Type::XLSX);
        $writer->openToFile($this->getPath());

        $header = $this->flatRowBuffer->getHeaders();
        $hollowItem = array_fill_keys($header, '');
        $writer->addRow($header);
        foreach ($this->flatRowBuffer->getBuffer() as $incompleteItem) {
            $item = array_replace($hollowItem, $incompleteItem);
            $writer->addRow($item);

            if (null !== $this->stepExecution) {
                $this->stepExecution->incrementSummaryInfo('write');
            }
        }

        $writer->close();
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return [
            'filePath' => [
                'options' => [
                    'label' => 'pim_connector.export.filePath.label',
                    'help'  => 'pim_connector.export.filePath.help',
                ],
            ],
            'withHeader' => [
                'type'    => 'switch',
                'options' => [
                    'label' => 'pim_connector.export.withHeader.label',
                    'help'  => 'pim_connector.export.withHeader.help',
                ],
            ],
        ];
    }

    /**
     * @return bool
     */
    public function isWithHeader()
    {
        return $this->withHeader;
    }

    /**
     * @param bool $withHeader
     */
    public function setWithHeader($withHeader)
    {
        $this->withHeader = $withHeader;
    }
}
