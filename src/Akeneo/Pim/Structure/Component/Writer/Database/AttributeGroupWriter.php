<?php

namespace Akeneo\Pim\Structure\Component\Writer\Database;

use Akeneo\Pim\Structure\Component\Repository\AttributeGroupRepositoryInterface;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\StorageUtils\Detacher\BulkObjectDetacherInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;

/**
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeGroupWriter implements ItemWriterInterface, StepExecutionAwareInterface
{
    /** @var StepExecution */
    protected $stepExecution;

    /** @var BulkSaverInterface */
    protected $bulkSaver;

    /** @var BulkObjectDetacherInterface */
    protected $bulkDetacher;

    /** @var AttributeGroupRepositoryInterface */
    protected $attributeGroupRepository;

    /**
     * @param BulkSaverInterface                $bulkSaver
     * @param BulkObjectDetacherInterface       $bulkDetacher
     * @param AttributeGroupRepositoryInterface $attributeGroupRepository
     */
    public function __construct(
        BulkSaverInterface $bulkSaver,
        BulkObjectDetacherInterface $bulkDetacher,
        AttributeGroupRepositoryInterface $attributeGroupRepository

    ) {
        $this->bulkSaver = $bulkSaver;
        $this->bulkDetacher = $bulkDetacher;
        $this->attributeGroupRepository = $attributeGroupRepository;
    }

    /**
     * {@inheritdoc}
     *
     * We need to save a default group because all attributes must have a group.
     *
     * For example, we want to remove the price attribute from the product information group. We must put it
     * in the default group so we make sure it is always saved
     */
    public function write(array $objects)
    {
        $this->incrementCount($objects);

        if (null !== $defaultGroup = $this->attributeGroupRepository->findDefaultAttributeGroup()) {
            $objects[] = $defaultGroup;
        }

        $this->bulkSaver->saveAll($objects);
        $this->bulkDetacher->detachAll($objects);
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * @param array $objects
     */
    protected function incrementCount(array $objects)
    {
        foreach ($objects as $object) {
            if ($object->getId()) {
                $this->stepExecution->incrementSummaryInfo('process');
            } else {
                $this->stepExecution->incrementSummaryInfo('create');
            }
        }
    }
}
