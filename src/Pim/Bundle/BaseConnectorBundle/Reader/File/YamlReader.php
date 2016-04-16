<?php

namespace Pim\Bundle\BaseConnectorBundle\Reader\File;

use Akeneo\Bundle\BatchBundle\Item\UploadedFileAwareInterface;
use Akeneo\Component\Batch\Item\FlushableInterface;
use Akeneo\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Pim\Component\Catalog\Validator\Constraints\File as AssertFile;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Yaml\Yaml;

/**
 * Yaml reader
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class YamlReader extends FileReader implements
    ItemReaderInterface,
    UploadedFileAwareInterface,
    StepExecutionAwareInterface,
    FlushableInterface
{
    /** @var string */
    protected $codeField = 'code';

    /** @var bool */
    protected $multiple = false;

    /** @var bool */
    protected $uploadAllowed = false;

    /** @var StepExecution */
    protected $stepExecution;

    /** @var \ArrayIterator */
    protected $yaml;

    /**
     * Constructor
     *
     * @param bool   $multiple
     * @param string $codeField
     */
    public function __construct($multiple = false, $codeField = 'code')
    {
        $this->codeField = $codeField;
        $this->multiple = $multiple;
    }

    /**
     * Set the code field
     *
     * @param string $codeField
     *
     * @return YamlReader
     */
    public function setCodeField($codeField)
    {
        $this->codeField = $codeField;

        return $this;
    }

    /**
     * Get the code field
     *
     * @return string
     */
    public function getCodeField()
    {
        return $this->codeField;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * {@inheritdoc}
     */
    public function getUploadedFileConstraints()
    {
        return [
            new Assert\NotBlank(),
            new AssertFile(
                [
                    'allowedExtensions' => ['yml', 'yaml']
                ]
            )
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @return YamlReader
     */
    public function setUploadedFile(File $uploadedFile)
    {
        // TODO: to fix!!
        $this->filePath = $uploadedFile->getRealPath();
        $this->yaml = null;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        if (null === $this->yaml) {
            $fileData = $this->getFileData();
            if (null === $fileData) {
                return null;
            }
            $this->yaml = new \ArrayIterator($fileData);
        }

        if ($data = $this->yaml->current()) {
            $this->yaml->next();

            if (null !== $this->stepExecution) {
                $this->stepExecution->incrementSummaryInfo('read_lines');
            }

            return $data;
        }

        return null;
    }

    /**
     * Returns the file data
     *
     * @return array
     */
    protected function getFileData()
    {
        $jobParameters = $this->stepExecution->getJobParameters();
        $filePath = $jobParameters->getParameter('filePath');
        $fileData = current(Yaml::parse(file_get_contents($filePath)));
        if (null === $fileData) {
            return null;
        }

        foreach ($fileData as $key => $row) {
            if ($this->codeField && !isset($row[$this->codeField])) {
                $fileData[$key][$this->codeField] = $key;
            }
        }

        return $this->multiple ? [$fileData] : $fileData;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return [
            'filePath' => [
                'options' => [
                    'label' => 'pim_base_connector.import.yamlFilePath.label',
                    'help'  => 'pim_base_connector.import.yamlFilePath.help'
                ]
            ],
            'uploadAllowed' => [
                'type'    => 'switch',
                'options' => [
                    'label' => 'pim_base_connector.import.uploadAllowed.label',
                    'help'  => 'pim_base_connector.import.uploadAllowed.help'
                ]
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function initialize()
    {
        if (null !== $this->yaml) {
            $this->yaml->rewind();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function flush()
    {
        $this->yaml = null;
    }
}
