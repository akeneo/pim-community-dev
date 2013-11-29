<?php

namespace Oro\Bundle\WorkflowBundle\Form\EventListener;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Oro\Bundle\WorkflowBundle\Model\WorkflowData;

/**
 * This listener removes attributes from workflow data if they are not present in form in PRE_SET_DATA event
 * and returns all values including submitted values back to original Workflow Data in SUBMIT event.
 *
 * This logic is used to avoid validation of attributes that are not in current form.
 */
class RequiredAttributesListener implements EventSubscriberInterface
{
    /**
     * @var WorkflowData
     */
    protected $workflowData;

    /**
     * @var array
     */
    protected $attributeNames;

    /**
     * @param array $attributeNames
     */
    public function initialize(array $attributeNames)
    {
        $this->attributeNames = $attributeNames;
    }

    /**
     * Extract only required attributes for form and create new WorkflowData based on them
     *
     * @param FormEvent $event
     */
    public function onPreSetData(FormEvent $event)
    {
        /** @var WorkflowData $data */
        $data = $event->getData();
        if ($data instanceof WorkflowData) {
            $this->workflowData = $data;
            $rawData = $data->getValues($this->attributeNames);
            $formData = new WorkflowData($rawData);
            $event->setData($formData);
        }
    }

    /**
     * Copy submitted data to existing workflow data
     *
     * @param FormEvent $event
     */
    public function onSubmit(FormEvent $event)
    {
        /** @var WorkflowData $formData */
        $formData = $event->getData();
        if ($this->workflowData && $formData instanceof WorkflowData) {
            $this->workflowData->add($formData->getValues());
            $event->setData($this->workflowData);
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(FormEvents::PRE_SET_DATA => 'onPreSetData', FormEvents::SUBMIT => 'onSubmit');
    }
}
