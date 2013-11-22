<?php

namespace Oro\Bundle\FormBundle\Form\Extension;

use Oro\Bundle\FormBundle\JsValidation\ConstraintsProvider;

use Oro\Bundle\FormBundle\JsValidation\JsValidationEvents;
use Oro\Bundle\FormBundle\JsValidation\Event;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;

class JsValidationExtension extends AbstractTypeExtension
{
    /**
     * @var array
     */
    protected $validationGroups;

    /**
     * @param ConstraintsProvider $constraintsProvider
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        ConstraintsProvider $constraintsProvider,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->constraintsProvider = $constraintsProvider;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $passableViewOptions = array(
            'error_mapping', 'inherit_data', 'data_class', 'constraints', 'property_path', 'mapped', 'validation_groups'
        );

        // Pass to form view options that will be used by this extension logic
        foreach ($passableViewOptions as $passableViewOption) {
            if (isset($options[$passableViewOption])) {
                $view->vars[$passableViewOption] = $options[$passableViewOption];
            }
        }

        // Begin to initialize form views with JS validation data starting from the most root element
        if (!$view->parent) {
            $this->validationGroups = $this->extractValidationGroups($view);
            $this->initFormViewJsValidationData($view);
            unset($this->validationGroups);
        }
    }

    /**
     * Initializes attributes of form view to pass JS validation data
     *
     * @param FormView $view
     */
    protected function initFormViewJsValidationData(FormView $view)
    {
        // Add "data-validation-optional-group" to form view if required.
        if ($this->isOptionalEmbeddedFormView($view)) {
            $view->vars['attr']['data-validation-optional-group'] = null;
        }

        // Add "data-validation" to form view
        $this->addDataValidationAttribute($view);

        if (isset($view->vars['prototype']) && $view->vars['prototype'] instanceof FormView) {
            $this->initFormViewJsValidationData($view->vars['prototype']);
        }

        foreach ($view->children as $child) {
            $this->initFormViewJsValidationData($child);
        }
    }

    /**
     * Detects if current form view represents an embedded form view that is optional, so validation will
     * run only if one of the children is filled in.
     *
     * @param FormView $view
     * @return bool
     */
    protected function isOptionalEmbeddedFormView(FormView $view)
    {
        // Should have children
        if (!$view->children) {
            return false;
        }

        // Should have parent
        if (!$view->parent) {
            return false;
        }

        // Should not be a field with choices, suchs as checkboxes, radio or select
        if (isset($view->vars['choices'])) {
            return false;
        }


        if ($view->vars['required']) {
            // if required should not inherit data
            if (!$view->vars['inherit_data']) {
                return false;
            }
        }

        // Should not be required
        if ($view->vars['required']) {
            return false;
        }

        return true;
    }

    /**
     * Adds attribute to form view that contain data for JS validation
     *
     * @param FormView $view
     */
    protected function addDataValidationAttribute(FormView $view)
    {
        $this->eventDispatcher->dispatch(JsValidationEvents::PRE_PROCESS, new Event\PreProcessEvent($view));

        $constraints = $this->constraintsProvider->getFormViewConstraints($view, $this->validationGroups);

        $this->eventDispatcher->dispatch(
            JsValidationEvents::GET_CONSTRAINTS_EVENT,
            new Event\GetConstraintsEvent(
                $view,
                $constraints
            )
        );

        $value = array();
        foreach ($constraints as $constraint) {
            $name = $this->constraintsProvider->getConstraintName($constraint);
            $value[$name] = $this->constraintsProvider->getConstraintProperties($constraint);
        }

        if ($value) {
            if (!isset($view->vars['attr'])) {
                $view->vars['attr'] = array();
            }
            if (!isset($view->vars['attr']['data-validation']) || !is_array($view->vars['attr']['data-validation'])) {
                $view->vars['attr']['data-validation'] = array();
            }
            $view->vars['attr']['data-validation'] = array_merge($value, $view->vars['attr']['data-validation']);
        }

        $this->eventDispatcher->dispatch(JsValidationEvents::POST_PROCESS, new Event\PostProcessEvent($view));

        if (isset($view->vars['attr']['data-validation']) && is_array($view->vars['attr']['data-validation'])) {
            $view->vars['attr']['data-validation'] = json_encode($view->vars['attr']['data-validation']);
        }
    }

    /**
     * Extracts validation groups option from form view
     *
     * @param FormView $view
     * @return array
     */
    protected function extractValidationGroups(FormView $view)
    {
        $validationGroups = isset($view->vars['validation_groups']) ?
            $view->vars['validation_groups'] : array('Default');

        if (is_callable($validationGroups)) {
            $validationGroups = call_user_func($validationGroups, $view);
        }
        return $validationGroups;
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return 'form';
    }
}
