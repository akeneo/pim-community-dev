<?php

namespace Pim\Bundle\ImportExportBundle\Reader\JSON;

use Symfony\Component\Validator\Constraints as Assert;
use Pim\Bundle\CatalogBundle\Validator\Constraints\File as AssertFile;
use Oro\Bundle\BatchBundle\Item\ItemReaderInterface;
use Oro\Bundle\BatchBundle\Item\UploadedFileAwareInterface;
use Oro\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Oro\Bundle\BatchBundle\Entity\StepExecution;
use Oro\Bundle\BatchBundle\Step\StepExecutionAwareInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\File;
use Doctrine\Common\Util\Inflector;
use Pim\Bundle\ImportExportBundle\Normalizer\ObjectAttributesNormalizer;

/**
 * Description of ProductReader
 *
 * @author wn-s.rascar
 */
class JSONReader extends AbstractConfigurableStepElement implements ItemReaderInterface, UploadedFileAwareInterface, StepExecutionAwareInterface
{

    const DATA_PARAMETER_NAME = 'data';

    /**
     * @Assert\NotBlank(groups={"Execution"})
     * @AssertFile(groups={"Execution"}, allowedExtensions={"txt"})
     */
    protected $filePath;
    protected $errors;
    protected $data;
    protected $uploadDirectory;
    protected $uploadAllowed;
    protected $normalizer;
    protected $currentIndex;

    public function __construct($uploadDirectory)
    {
        $this->normalizer = new ObjectAttributesNormalizer();
        $this->currentIndex = 0;
        $this->errors = array();
        $this->uploadDirectory = $uploadDirectory;
    }

    protected function normalizeData($data)
    {
        foreach ($data as $key => $item) {
            $data[$key] = $this->normalizer->normalize($item);
        }
        return $data;
    }

    protected function getCurrentItem()
    {
        if(isset($this->data[$this->currentIndex])){
            $item = $this->data[$this->currentIndex];
            $this->currentIndex++;
            return $item;
        }
        $this->data = null;
        $this->currentIndex = 0;
        return null;
    }

    public function getUploadedFileConstraints()
    {
        return array();
    }

    public function read()
    {

        $data = $this->data;

        if (!empty($data)) {
            return $this->getCurrentItem();
            
        } elseif ($this->filePath) {
            $jsonFile = new \SplFileObject($this->filePath);
            $data = $jsonFile->fgets();

            if ($data !== false) {
                while (!$jsonFile->eof()) {
                    $data .= $jsonFile->fgets();
                }
                $data = json_decode($data);
                $this->data = $this->normalizeData($data);
                return $this->getCurrentItem();
            } else {
                throw new \RuntimeException('An error occured while reading the uploaded file.');
            }
        }
        return null;
    }

    public function handleRequest(Request $request, $dataParameterName = null)
    {
        if (!$dataParameterName) {
            $dataParameterName = self::DATA_PARAMETER_NAME;
        }
        if (!empty($this->data)) {
            $this->errors[] = 'elle_json_connector.import.service_currently_running';
        }
        $data = json_decode($request->request->get($dataParameterName));
        
        $this->data = $this->normalizeData($data);

        return true;
    }

    public function getErrors()
    {
        $errors = $this->errors;
        $this->errors = array();
        return $errors;
    }

    /**
     * Set uploaded file
     * @param string $uploadedFile
     *
     * @return JsonReader
     */
    public function setUploadedFile(File $uploadedFile)
    {
        $this->filePath = $uploadedFile->getRealPath();
        return $this;
    }

    /**
     * Set file path
     * @param string $filePath
     *
     * @return JsonReader
     */
    public function setFilePath($filePath)
    {
        $this->filePath = $filePath;
        return $this;
    }

    /**
     * Get file path
     * @return string $filePath
     */
    public function getFilePath()
    {
        return $this->filePath;
    }

    /**
     * Set uploadAllowed
     * @param boolean $uploadAllowed
     *
     * @return JsonReader
     */
    public function setUploadAllowed($uploadAllowed)
    {
        $this->uploadAllowed = $uploadAllowed;
        return $this;
    }

    /**
     * Get uploadAllowed
     * @return boolean $uploadAllowed
     */
    public function getUploadAllowed()
    {
        return $this->uploadAllowed;
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
    public function getConfigurationFields()
    {
        return array(
            'filePath' => array(),
            'uploadAllowed' => array(
                'type' => 'switch',
            ),
        );
    }

    /**
     * Return name
     *
     * @return string
     */
    public function getName()
    {
        $classname = get_class($this);

        if (preg_match('@\\\\([\w]+)$@', $classname, $matches)) {
            $classname = $matches[1];
        }

        return Inflector::tableize($classname);
    }

}
