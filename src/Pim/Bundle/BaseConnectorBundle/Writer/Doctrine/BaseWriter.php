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

    /** @var bool */
    protected $detachesObjects;

    /**
     * @param BulkSaverInterface      $bulkSaver
     * @param ObjectDetacherInterface $objectDetacher
     * @param bool                    $detachesObjects
     */
    public function __construct(
        BulkSaverInterface $bulkSaver,
        ObjectDetacherInterface $objectDetacher,
        $detachesObjects = false
    ) {
        $this->bulkSaver = $bulkSaver;
        $this->objectDetacher = $objectDetacher;
        $this->detachesObjects = $detachesObjects;
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $objects)
    {
        echo count($objects).PHP_EOL;
        $this->bulkSaver->saveAll($objects);

        // TODO: we should always detach objects we just processed, unfortunately, we encounter issues with flush()
        // and fact that some subscribers as AddVersionListener loads others objects, for instance, when import a new
        // attribute option, we detach the option, which is reloaded to handle the versioning of an attribute and
        // inserted twice
        if (true === $this->detachesObjects) {
            foreach ($objects as $object) {
                $this->objectDetacher->detach($object);
            }
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
}
