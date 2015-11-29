<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Form\Applier;

use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use PimEnterprise\Bundle\WorkflowBundle\Event\ProductDraftEvent;
use PimEnterprise\Bundle\WorkflowBundle\Event\ProductDraftEvents;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraftInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Exception\ValidatorException;

/**
 * Applies product changes
 *
 * @author Gildas Quemener <gildas@akeneo.com>
 */
class ProductDraftChangesApplier
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
     * Apply product draft to a product
     *
     * @param ProductInterface      $product
     * @param ProductDraftInterface $productDraft
     *
     * @throws ValidatorException
     */
    public function apply(ProductInterface $product, ProductDraftInterface $productDraft)
    {
        if ($this->dispatcher->hasListeners(ProductDraftEvents::PRE_APPLY)) {
            $event = $this->dispatcher->dispatch(
                ProductDraftEvents::PRE_APPLY,
                new ProductDraftEvent($productDraft)
            );
        }

        /** @var FormInterface $form */
        $form = $this
            ->formFactory
            ->createBuilder('form', $product, ['csrf_protection' => false])
            ->add(
                'values',
                'pim_enrich_localized_collection',
                [
                    'type'               => 'pim_product_value',
                    'allow_add'          => false,
                    'allow_delete'       => false,
                    'by_reference'       => false,
                    'cascade_validation' => true,
                    'currentLocale'      => null,
                    'comparisonLocale'   => null,
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

        $form->submit($productDraft->getChanges(), false);

        if (null !== $error = $this->getFormErrorsAsString($form)) {
            throw new ValidatorException($error);
        }
    }

    /**
     * Wether or not a a product value is marked as modified (meaning a product draft has changed its value)
     *
     * @param array  $attribute The attribute as stored in the product form view
     * @param string $scope     The scope to check
     *
     * @return bool
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
     * @param ProductValueInterface $value
     */
    protected function markValueAsModified(ProductValueInterface $value)
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
     * Get all errors of the form by scanning children. The returned array is like this :
     * [
     *      field 1 => [FormError 1, FormError 2]
     *      field 2 => [FormError 1, FormError 2, FormError3]
     * ]
     *
     * @param FormInterface $form
     * @param FormInterface $parent
     * @param array         &$errors
     *
     * @return \Symfony\Component\Form\FormError[]
     */
    protected function getFormErrors(FormInterface $form, FormInterface $parent = null, array &$errors = [])
    {
        if (null !== $parent) {
            foreach ($form->getErrors() as $error) {
                $errors[$parent->getName()][] = $error;
            }
        }

        foreach ($form->all() as $child) {
            $this->getFormErrors($child, $form, $errors);
        }

        return $errors;
    }

    /**
     * Get the errors of the form as a string.
     *
     * @param FormInterface $form
     *
     * @return null|string
     */
    protected function getFormErrorsAsString(FormInterface $form)
    {
        $errorAsString = '';

        foreach ($this->getFormErrors($form) as $field => $errors) {
            foreach ($errors as $error) {
                $errorAsString .= sprintf('%s: %s ', ucfirst($field), $error->getMessage());
            }
        }

        return '' === $errorAsString ? null : $errorAsString;
    }
}
