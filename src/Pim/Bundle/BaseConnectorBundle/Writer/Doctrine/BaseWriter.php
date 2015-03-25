<?php

namespace Pim\Bundle\BaseConnectorBundle\Writer\Doctrine;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Akeneo\Bundle\BatchBundle\Item\ItemWriterInterface;
use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;

/**
 * Generic writer for basic objects, writes them in DB and detach them from the UOW to free the memory
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BaseWriter extends AbstractConfigurableStepElement implements
    ItemWriterInterface,
    StepExecutionAwareInterface
{
    /** @var StepExecution */
    protected $stepExecution;

    /** @var BulkSaverInterface */
    protected $bulkSaver;

    /** @var ObjectDetacherInterface */
    protected $objectDetacher;

    /**
     * @param BulkSaverInterface      $bulkSaver
     * @param ObjectDetacherInterface $objectDetacher
     */
    public function __construct(
        BulkSaverInterface $bulkSaver,
        ObjectDetacherInterface $objectDetacher
    ) {
        $this->bulkSaver = $bulkSaver;
        $this->objectDetacher = $objectDetacher;
        $this->total = 0; // TODO: to drop, debug purpose
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $objects)
    {
        $this->total = count($objects) + $this->total;
        echo $this->convert(memory_get_usage(true)).' write '.count($objects).'/'.$this->total.PHP_EOL;

        $this->bulkSaver->saveAll($objects);

        // TODO: we should always detach objects we just processed, unfortunately, we encounter issues with flush()
        // and fact that some subscribers as AddVersionListener loads others objects, for instance, when import a new
        // attribute option, we detach the option, which is reloaded to handle the versioning of an attribute and
        // inserted twice, with DEFERRED_EXPLICIT it works well !!!
        foreach ($objects as $object) {
            $this->objectDetacher->detach($object);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return array();
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * TODO: to drop, debug purpose
     *
     * @param $size
     * @return string
     */
    public function convert($size)
    {
        $unit=array('b','kb','mb','gb','tb','pb');

        return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
    }
}
