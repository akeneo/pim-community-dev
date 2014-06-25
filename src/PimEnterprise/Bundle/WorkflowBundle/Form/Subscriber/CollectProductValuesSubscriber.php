<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Form\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Security\Core\SecurityContextInterface;
use PimEnterprise\Bundle\WorkflowBundle\Form\Comparator\ComparatorInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;

/**
 * A collector of changes that a client is sending to a product edit form
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class CollectProductValuesSubscriber implements EventSubscriberInterface
{
    /** @var ComparatorInterface */
    protected $comparator;

    /** @var array */
    protected $changes = [];

    /** @var SecurityContextInterface */
    protected $securityContext;

    /**
     * @param ComparatorInterface      $comparator
     * @param SecurityContextInterface $securityContext
     */
    public function __construct(
        ComparatorInterface $comparator,
        SecurityContextInterface $securityContext
    ) {
        $this->comparator = $comparator;
        $this->securityContext = $securityContext;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SUBMIT => 'collect',
        ];
    }

    /**
     * Collect changes that client sent to the product values
     *
     * @param FormEvent $event
     */
    public function collect(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        if ('pim_product_edit' !== $form->getName()
            || !array_key_exists('values', $data)
            || $this->securityContext->isGranted(Attributes::OWNER, $form->getData())) {
            return;
        }

        $currentValues = $form->get('values')->getViewData();
        foreach ($data['values'] as $key => $value) {
            if ($currentValues->containsKey($key)) {
                if (null !== $changes = $this->comparator->getChanges($currentValues->get($key), $value)) {
                    $this->changes['values'][$key] = $changes;
                }
            }
        }
    }

    /**
     * Get the collected changes sent to the product values
     *
     * @return array
     */
    public function getChanges()
    {
        return $this->changes;
    }
}
