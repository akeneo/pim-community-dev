<?php

namespace Pim\Component\Connector\Writer\File;

/**
 * CSV variant group writer
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CsvVariantGroupWriter extends CsvWriter
{
    /** @var BulkFileExporter */
    protected $fileExporter;

    /**
     * @param FilePathResolverInterface $filePathResolver
     * @param FlatItemBuffer            $flatRowBuffer
     * @param BulkFileExporter          $fileExporter
     */
    public function __construct(
        FilePathResolverInterface $filePathResolver,
        FlatItemBuffer $flatRowBuffer,
        BulkFileExporter $fileExporter
    ) {
        parent::__construct($filePathResolver, $flatRowBuffer);

        $this->fileExporter = $fileExporter;
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

        $variantGroups = $media = [];
        foreach ($items as $item) {
            $variantGroups[] = $item['variant_group'];
            $media[]         = $item['media'];
        }

        parent::write($variantGroups);
        $this->fileExporter->exportAll($media, $exportDirectory);

        foreach ($this->fileExporter->getCopiedMedia() as $copy) {
            $this->writtenFiles[$copy['copyPath']] = $copy['originalMedium']['exportPath'];
        }

        foreach ($this->fileExporter->getErrors() as $error) {
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
    public function setConfiguration(array $config)
    {
        parent::setConfiguration($config);

        if (!isset($config['mainContext'])) {
            return;
        }

        foreach ($config['mainContext'] as $key => $value) {
            $this->filePathResolverOptions['parameters']['%' . $key . '%'] = $value;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return array_merge(
            parent::getConfigurationFields(),
            [
                'filePath' => [
                    'options' => [
                        'label' => 'pim_connector.export.filePath.label',
                        'help'  => 'pim_connector.export.filePath.help',
                    ],
                ],
            ]
        );
    }
}
