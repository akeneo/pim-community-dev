<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Persistence;

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Pim\Bundle\CatalogBundle\Model\AbstractProduct;
use PimEnterprise\Bundle\WorkflowBundle\EventDispatcher\PropositionEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;

/**
 * Applies product changes
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ProductChangesApplier
{
    /** @var FormFactoryInterface */
    protected $formFactory;

    /** @var EventDispatcherInterface */
    protected $dispatcher;

    /** @var array */
    protected $modifiedFields = [];

    /**
     * @param FormFactoryInterface     $formFactory
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        EventDispatcherInterface $dispatcher
    ) {
        $this->formFactory = $formFactory;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Apply changes to a product
     *
     * @param AbstractProduct $product
     * @param array           $changes
     */
    public function apply(AbstractProduct $product, array $changes)
    {
        if ($this->dispatcher->hasListeners(PropositionEvent::BEFORE_APPLY_CHANGES)) {
            $event = $this->dispatcher->dispatch(
                PropositionEvent::BEFORE_APPLY_CHANGES,
                new PropositionEvent($changes)
            );
            $changes = $event->getChanges();
        }

        $this
            ->formFactory
            ->createBuilder('form', $product)
            ->add(
                'values',
                'pim_enrich_localized_collection',
                [
                    'type' => 'pim_product_value',
                    'allow_add' => false,
                    'allow_delete' => false,
                    'by_reference' => false,
                    'cascade_validation' => true,
                    'currentLocale' => null,
                    'comparisonLocale' => null,
                ]
            )
            ->addEventListener(
                FormEvents::PRE_SUBMIT,
                function(FormEvent $event) {
                    $data = $event->getData();
                    $form = $event->getForm();
                    $values = $form->get('values');

                    foreach ($values as $key => $value) {
                        if (isset($data['values'][$key])) {
                            $this->markFieldAsModified($value);
                        }
                    }
                }
            )
            ->getForm()
            ->submit($changes, false);
    }

    public function markFieldAsModified(FormInterface $form)
    {
        $this->modifiedFields[] = $form->getName();
    }

    public function isMarkedAsModified($fieldName)
    {
        return in_array($fieldName, $this->modifiedFields);
    }
}
