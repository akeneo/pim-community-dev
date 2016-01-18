<?php

namespace Pim\Bundle\BaseConnectorBundle\Writer\Doctrine;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Akeneo\Bundle\BatchBundle\Item\ItemWriterInterface;
use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Pim\Bundle\TransformBundle\Cache\CacheClearer;

/**
 * Generic writer for basic entities
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @deprecated will be removed in 1.5, please use the BaseWriter to avoid to have persist()/flush() calls everywhere in
 * the stack, we prefer rely on the BulkSaverInterface to decouple from Doctrine, you can also notice that we don't use
 * anymore massive cache clearer, the changeTrackingPolicy: DEFERRED_EXPLICIT allows to avoid magic around persistence
 */
class Writer extends AbstractConfigurableStepElement implements
    ItemWriterInterface,
    StepExecutionAwareInterface
{
    /** @var ManagerRegistry */
    protected $registry;

    /**@var CacheClearer */
    protected $cacheClearer;

    /** @var StepExecution */
    protected $stepExecution;

    /**
     * @param ManagerRegistry $registry
     * @param CacheClearer    $cacheClearer
     */
    public function __construct(
        ManagerRegistry $registry,
        CacheClearer $cacheClearer
    ) {
        $this->registry     = $registry;
        $this->cacheClearer = $cacheClearer;
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
                    sprintf(
                        'Expecting item of type object, got "%s"',
                        gettype($item)
                    )
                );
            }

            $this->registry->getManagerForClass(get_class($item))->persist($item);
            $this->incrementCount($item);
        }

        foreach ($this->registry->getManagers() as $manager) {
            $manager->flush();
        }

        $this->cacheClearer->clear();
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
    public function flush()
    {
        $this->cacheClearer->clear(true);
    }

    /**
     * @param object $item
     */
    protected function incrementCount($item)
    {
        if ($item->getId()) {
            $this->stepExecution->incrementSummaryInfo('process');
        } else {
            $this->stepExecution->incrementSummaryInfo('create');
        }
    }
}
