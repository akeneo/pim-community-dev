<?php

namespace Oro\Bundle\FormBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraint;

use Oro\Bundle\FormBundle\JsValidation\ConstraintsProvider;

class JsValidationExtension extends AbstractTypeExtension
{
    /**
     * @var ConstraintsProvider
     */
    protected $eventDispatcher;

    /**
     * @param ConstraintsProvider $constraintsProvider
     */
    public function __construct(ConstraintsProvider $constraintsProvider)
    {
        $this->constraintsProvider = $constraintsProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $this->addDataValidationOptionalGroupAttribute($view, $options);
        $this->addDataValidationAttribute($view, $form);
    }

    /**
     * Adds "data-validation-optional-group" attribute to embedded form.
     *
     * Validation will run only if one of the children is filled in.
     *
     * @param FormView $view
     * @param array $options
     */
    protected function addDataValidationOptionalGroupAttribute(FormView $view, array $options)
    {
        if ($this->isOptionalEmbeddedFormView($view, $options)) {
            $view->vars['attr']['data-validation-optional-group'] = null;
        }
    }

    /**
     * Detects if current form view represents an embedded form view that is optional, so validation will
     * run only if one of the children is filled in.
     *
     * @param FormView $view
     * @param array $options
     * @return bool
     */
    protected function isOptionalEmbeddedFormView(FormView $view, array $options)
    {
        // Optional embedded form view should have children
        if (!$view->children) {
            return false;
        }

        // Optional embedded form view should have parent
        if (!$view->parent) {
            return false;
        }

        // Optional embedded form view should not be a field with choices, such as checkboxes, radio or select
        if (isset($options['choices'])) {
            return false;
        }

        // Optional embedded form view should not be required
        if (isset($options['required']) && $options['required']) {
            // Except case when it's inherit data
            if (isset($options['inherit_data']) && $options['inherit_data']) {
                return true;
            }
            return false;
        }

        return true;
    }

    /**
     * Adds "data-validation" attribute to form view that contain data for JS validation
     *
     * @param FormView $view
     * @param FormInterface $form
     */
    protected function addDataValidationAttribute(FormView $view, FormInterface $form)
    {
        $data = $this->getConstraintsDataAsArray($form);

        if ($data) {
            if (!isset($view->vars['attr'])) {
                $view->vars['attr'] = array();
            }
            if (isset($view->vars['attr']['data-validation'])) {
                $originalData = $view->vars['attr']['data-validation'];
                if (is_array($originalData)) {
                    $data = array_merge($originalData, $data);
                } elseif (is_string($originalData)) {
                    $originalData = json_decode($originalData, true);
                    if (is_array($originalData)) {
                        $data = array_merge($originalData, $data);
                    }
                }
            }
            $view->vars['attr']['data-validation'] = $data;
        }

        if (isset($view->vars['attr']['data-validation']) && is_array($view->vars['attr']['data-validation'])) {
            $view->vars['attr']['data-validation'] = json_encode(
                $view->vars['attr']['data-validation'],
                JSON_FORCE_OBJECT
            );
        }
    }

    /**
     * @param FormInterface $form
     * @return array
     */
    protected function getConstraintsDataAsArray(FormInterface $form)
    {
        $constraints = $this->constraintsProvider->getFormConstraints($form);

        $value = array();
        foreach ($constraints as $constraint) {
            $name = $this->getConstraintName($constraint);
            $value[$name] = $this->getConstraintProperties($constraint);
        }

        return $value;
    }

    /**
     * Gets constraint name based on object
     *
     * @param Constraint $constraint
     * @return mixed|string
     */
    protected function getConstraintName(Constraint $constraint)
    {
        $class = get_class($constraint);
        $defaultClassPrefix = 'Symfony\\Component\\Validator\\Constraints\\';
        if (0 === strpos($class, $defaultClassPrefix)) {
            return str_replace($defaultClassPrefix, '', $class);
        }
        return $class;
    }

    /**
     * Gets all properties of constraint that will be passed to JS validation
     *
     * @param Constraint $constraint
     * @return array
     */
    protected function getConstraintProperties(Constraint $constraint)
    {
        $result = get_object_vars($constraint);
        unset($result['groups']);
        return $this->skipObjects($result);
    }

    /**
     * @param array $value
     * @return array
     */
    protected function skipObjects(array $value)
    {
        foreach ($value as $key => $element) {
            if (is_object($element)) {
                unset($value[$key]);
            }
            if (is_array($element)) {
                $value[$key] = $this->skipObjects($element);
            }
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return 'form';
    }
}
