<?php

namespace Oro\Bundle\FormBundle\Form\Extension;

use Symfony\Component\Validator\MetadataFactoryInterface;
use Symfony\Component\Validator\Constraint;

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
     * @var array
     */
    protected $metadataConstraintsCache;

    /**
     * @param MetadataFactoryInterface $metadataFactory
     */
    public function __construct(MetadataFactoryInterface $metadataFactory)
    {
        $this->metadataFactory = $metadataFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        // Pass to form view options that will be used by this extension logic
        foreach (array('error_mapping', 'inherit_data') as $passableViewOption) {
            if (isset($options[$passableViewOption])) {
                $view->vars[$passableViewOption] = $options[$passableViewOption];
            }
        }

        // Begin to initialize form views with JS validation data starting from the most root element
        if (!$view->parent) {
            $this->validationGroups = $this->extractValidationGroups($view);
            $this->initFormViewJsValidationData($view);
            unset($this->validationGroups);
            unset($this->metadataConstraintsCache);
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

        //
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
        $constraints = $this->getFormViewConstraints($view);

        $value = array();
        foreach ($constraints as $constraint) {
            $value[$this->getConstraintName($constraint)] = $this->getConstraintProperties($constraint);
        }

        if ($value) {
            $target = $view;
            // @TODO Move this logic of handling repeated form type outside of this extension class, use events instead
            if (isset($view->vars['type']) && $view->vars['type'] == 'repeated') {
                $repeatedNames = array_keys($view->vars['value']);
                $target = $view->children[$repeatedNames[0]];

                $secondValue = array();
                $secondValue['Repeated'] = array(
                    'first_name' => $repeatedNames[0],
                    'second_name' => $repeatedNames[1],
                    'invalid_message' => $view->vars['invalid_message'],
                    'invalid_message_parameters' => $view->vars['invalid_message_parameters'],
                );
                $second = $view->children[$repeatedNames[1]];

                if (!isset($second->vars['attr'])) {
                    $second->vars['attr'] = array();
                }
                $second->vars['attr']['data-validation'] = json_encode($secondValue);
            }
            if (!isset($target->vars['attr'])) {
                $target->vars['attr'] = array();
            }
            $target->vars['attr']['data-validation'] = json_encode($value);
        }
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
        foreach ($result as $key => $value) {
            if (is_object($value)) {
                unset($result[$key]);
            }
        }
        return $result;
    }

    /**
     * Gets constraints that should be checked on form view
     *
     * @param FormView $view
     * @return array
     */
    protected function getFormViewConstraints(FormView $view)
    {
        $constraints = $this->getMetadataConstraints($view);

        if (isset($view->vars['constraints'])) {
            $constraints = array_merge($constraints, $view->vars['constraints']);
        }

        $result = array();
        foreach ($constraints as $constraint) {
            if (array_intersect($this->validationGroups, $constraint->groups)) {
                $result[] = $constraint;
            }
        }

        return $result;
    }

    /**
     * Gets constraints for form view based on metadata
     *
     * @param FormView $view
     * @return array
     */
    protected function getMetadataConstraints(FormView $view)
    {
        $isMapped = (!isset($view->vars['mapped']) || $view->vars['mapped']);

        if (!$view->parent || !$isMapped) {
            return array();
        }

        $name = $view->vars['name'];
        $parentName = $view->parent->vars['full_name'];

        if (!isset($this->metadataConstraintsCache[$parentName])) {
            $this->metadataConstraintsCache[$parentName] = $this->extractMetadataPropertiesConstraints($view->parent);
        }

        $result = array();

        if (isset($this->metadataConstraintsCache[$parentName][$name])) {
            $result = $this->metadataConstraintsCache[$parentName][$name]->constraints;
        }

        return $result;
    }

    /**
     * Extracts constraints based on validation metadata
     *
     * @param FormView $view
     * @return array
     */
    protected function extractMetadataPropertiesConstraints(FormView $view)
    {
        $constraints = array();
        if (isset($view->vars['data_class'])) {
            $metadata = $this->metadataFactory->getMetadataFor($view->vars['data_class']);
            $constraints = $metadata->properties;
        }
        if (!empty($constraints) && !empty($view->vars['error_mapping'])) {
            foreach ($view->vars['error_mapping'] as $originalName => $mappedName) {
                if (isset($constraints[$originalName])) {
                    $constraints[$mappedName] = $constraints[$originalName];
                }
            }
        }

        return $constraints;
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
