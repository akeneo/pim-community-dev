<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Form\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Security\Core\SecurityContextInterface;
use PimEnterprise\Bundle\WorkflowBundle\Proposition\ChangesCollectorAwareInterface;
use PimEnterprise\Bundle\WorkflowBundle\Proposition\ChangesCollectorInterface;
use PimEnterprise\Bundle\WorkflowBundle\Form\Comparator\ComparatorInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;

/**
 * A collector of changes that a client is sending to a product edit form
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class CollectProductValuesSubscriber implements
    ChangesCollectorAwareInterface,
    EventSubscriberInterface
{
    /** @var ComparatorInterface */
    protected $comparator;

    /** @var array */
    protected $changes = [];

    /** @var SecurityContextInterface */
    protected $securityContext;

    /** @var ChangesCollectorInterface */
    protected $collector;

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
    public function setCollector(ChangesCollectorInterface $collector)
    {
        $this->collector = $collector;
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
        foreach ($data['values'] as $key => $data) {
            if ($currentValues->containsKey($key)) {
                $value = $currentValues->get($key);
                $this->collector->add(
                    $key,
                    $this->comparator->getChanges($value, $data),
                    $value
                );
            }
        }
    }
}
