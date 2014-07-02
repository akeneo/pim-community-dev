<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Form\Applier;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Validator\Exception\ValidatorException;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\AbstractProductValue;
use PimEnterprise\Bundle\WorkflowBundle\Event\PropositionEvents;
use PimEnterprise\Bundle\WorkflowBundle\Event\PropositionEvent;
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

    /** @var array */
    protected $errors = [];

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
     * @param Proposition      $proposition
     *
     * @throws ValidatorException
     */
    public function apply(ProductInterface $product, Proposition $proposition)
    {
        if ($this->dispatcher->hasListeners(PropositionEvents::PRE_APPLY)) {
            $event = $this->dispatcher->dispatch(
                PropositionEvents::PRE_APPLY,
                new PropositionEvent($proposition)
            );
        }

        /** @var Form $form */
        $form = $this
            ->formFactory
            ->createBuilder('form', $product, ['csrf_protection' => false])
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
            ->getForm();

        $form->submit($proposition->getChanges(), false);

        if (null !== $error = $this->getFormErrorsAsString($form)) {
            throw new ValidatorException($error);
        }
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

    /**
     * Check all children of the form to get the errors.
     * Errors are stored in $this->errors.
     *
     * @param Form $form
     * @param Form $parent
     */
    protected function checkFormErrors(Form $form, Form $parent= null)
    {
        if (null !== $parent) {
            foreach ($form->getErrors() as $error) {
                $this->errors[$parent->getName()][] = $error->getMessage();
            }
        }

        foreach ($form->all() as $child) {
            $this->checkFormErrors($child, $form);
        }
    }

    /**
     * Get the errors of the form as a string.
     *
     * @param Form $form
     *
     * @return null|string
     */
    protected function getFormErrorsAsString(Form $form)
    {
        $error = '';
        $this->checkFormErrors($form);

        foreach ($this->errors as $field => $errors) {
            foreach ($errors as $message) {
                $error .= sprintf('%s: %s ', ucfirst($field), $message);
            }
        }

        return '' === $error ? null : $error;
    }
}
