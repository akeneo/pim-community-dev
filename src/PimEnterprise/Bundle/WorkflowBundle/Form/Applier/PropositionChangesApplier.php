<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Form\Applier;

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\AbstractProductValue;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use PimEnterprise\Bundle\WorkflowBundle\Proposition\PropositionEvents;
use PimEnterprise\Bundle\WorkflowBundle\Proposition\PropositionEvent;
use PimEnterprise\Bundle\WorkflowBundle\Model\Proposition;

/**
 * Applies product changes
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class PropositionChangesApplier
{
    /** @var FormFactoryInterface */
    protected $formFactory;

    /** @var EventDispatcherInterface */
    protected $dispatcher;

    /** @var array */
    protected $modifiedValues = [];

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
     * Apply proposition to a product
     *
     * @param ProductInterface $product
     * @param Proposition     $proposition
     */
    public function apply(ProductInterface $product, Proposition $proposition)
    {
        if ($this->dispatcher->hasListeners(PropositionEvents::PRE_APPLY)) {
            $event = $this->dispatcher->dispatch(
                PropositionEvents::PRE_APPLY,
                new PropositionEvent($proposition)
            );
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
                function (FormEvent $event) {
                    $data = $event->getData();
                    $form = $event->getForm();
                    $values = $form->get('values');

                    foreach ($values as $key => $value) {
                        if (isset($data['values'][$key])) {
                            $this->markValueAsModified($value->getData());
                        }
                    }
                }
            )
            ->getForm()
            ->submit($proposition->getChanges(), false);
    }

    /**
     * Wether or not a a product value is marked as modified (meaning a proposition has changed its value)
     *
     * @param array  $attribute The attribute as stored in the product form view
     * @param string $scope
     *
     * @return boolean
     */
    public function isMarkedAsModified($attribute, $scope = null)
    {
        $hasAttribute = array_key_exists($attribute['code'], $this->modifiedValues);

        if ($hasAttribute
            && null !== $scope
            && !in_array($scope, $this->modifiedValues[$attribute['code']]['scopes'])) {
            return false;
        }

        if ($hasAttribute
            && isset($attribute['locale'])
            && !in_array($attribute['locale'], $this->modifiedValues[$attribute['code']]['locales'])) {
            return false;
        }

        return $hasAttribute;
    }

    /**
     * Mark a value as modified
     *
     * @param AbstractProductValue $value
     */
    protected function markValueAsModified(AbstractProductValue $value)
    {
        $options = [];
        $attribute = $value->getAttribute();
        $key = $attribute->getCode();

        if ($attribute->isScopable()) {
            if (isset($this->modifiedValues[$key]['scopes'])) {
                $options['scopes'] = $this->modifiedValues[$key]['scopes'];
            }
            $options['scopes'][] = $value->getScope();
        }

        if ($attribute->isLocalizable()) {
            if (isset($this->modifiedValues[$key]['locales'])) {
                $options['locales'] = $this->modifiedValues[$key]['locales'];
            }
            $options['locales'][] = $value->getLocale();
        }

        $this->modifiedValues[$key] = $options;
    }
}
