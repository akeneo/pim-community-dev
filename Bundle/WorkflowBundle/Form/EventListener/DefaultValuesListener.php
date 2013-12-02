<?php

namespace Oro\Bundle\WorkflowBundle\Form\EventListener;

use Oro\Bundle\WorkflowBundle\Model\ContextAccessor;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Model\WorkflowData;

class DefaultValuesListener implements EventSubscriberInterface
{
    /**
     * @var ContextAccessor $contextAccessor
     */
    protected $contextAccessor;

    /**
     * @var WorkflowItem
     */
    protected $workflowItem;

    /**
     * @var array
     */
    protected $defaultValues;

    /**
     * @param ContextAccessor $contextAccessor
     */
    public function __construct(ContextAccessor $contextAccessor)
    {
        $this->contextAccessor = $contextAccessor;
    }

    /**
     * Initialize listener with required data
     *
     * @param WorkflowItem $workflowItem
     * @param array $defaultValues
     */
    public function initialize(
        WorkflowItem $workflowItem,
        array $defaultValues = array()
    ) {
        $this->workflowItem = $workflowItem;
        $this->defaultValues = $defaultValues;
    }

    /**
     * Updates default values
     *
     * @param FormEvent $event
     */
    public function setDefaultValues(FormEvent $event)
    {
        /** @var WorkflowData $workflowData */
        $workflowData = $event->getData();

        foreach ($this->defaultValues as $attributeName => $value) {
            $workflowData->set($attributeName, $this->contextAccessor->getValue($this->workflowItem, $value));
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(FormEvents::PRE_SET_DATA => 'setDefaultValues');
    }
}
