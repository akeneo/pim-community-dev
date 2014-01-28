<?php

namespace Pim\Bundle\BaseConnectorBundle\Writer\ORM;

use Doctrine\ORM\EntityManager;

use Oro\Bundle\BatchBundle\Entity\StepExecution;
use Oro\Bundle\BatchBundle\Item\ItemWriterInterface;
use Oro\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Oro\Bundle\BatchBundle\Step\StepExecutionAwareInterface;

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
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var StepExecution
     */
    protected $stepExecution;

    /**
     * Constructor
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
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

        foreach ($items as $entity) {
            $this->em->persist($entity);
            $this->incrementCount($entity);
        }

        $this->em->flush();

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
     * @param object $entity
     */
    protected function incrementCount($entity)
    {
        if ($entity->getId()) {
            $this->stepExecution->incrementSummaryInfo('update');
        } else {
            $this->stepExecution->incrementSummaryInfo('create');
        }
    }
}
