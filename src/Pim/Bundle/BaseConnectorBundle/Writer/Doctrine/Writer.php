<?php

namespace Pim\Bundle\BaseConnectorBundle\Writer\Doctrine;

use Doctrine\Common\Persistence\ManagerRegistry;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\ItemWriterInterface;
use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;

/**
 * Generic writer for basic entities
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Writer extends AbstractConfigurableStepElement implements
    ItemWriterInterface,
    StepExecutionAwareInterface
{
    /** @var ManagerRegistry */
    protected $registry;

    /** @var StepExecution */
    protected $stepExecution;

    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
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
    public function write(array $items)
    {
        if (is_array(reset($items))) {
            $items = call_user_func_array('array_merge', $items);
        }

        foreach ($items as $item) {
            if (!is_object($item)) {
                throw new \InvalidArgumentException(
                    'Expecting item of type object, got "%s"',
                    gettype($item)
                );
            }

            $this->registry->getManagerForClass(get_class($item))->persist($item);
            $this->incrementCount($item);
        }

        foreach ($this->registry->getManagers() as $manager) {
            $manager->flush();
        }

        $this->postWrite();
    }

    /**
     * Post write method
     */
    protected function postWrite()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * @param object $item
     */
    protected function incrementCount($item)
    {
        if ($item->getId()) {
            $this->stepExecution->incrementSummaryInfo('update');
        } else {
            $this->stepExecution->incrementSummaryInfo('create');
        }
    }
}
