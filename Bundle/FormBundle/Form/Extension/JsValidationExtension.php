<?php

namespace Oro\Bundle\FormBundle\Form\Extension;

use Symfony\Component\Validator\MetadataFactoryInterface;
use Symfony\Component\Validator\MetadataInterface;
use Symfony\Component\Validator\PropertyMetadataInterface;
use Symfony\Component\Validator\Constraint;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;

class JsValidationExtension extends AbstractTypeExtension
{
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
        if (isset($options['error_mapping'])) {
            $view->vars['error_mapping'] = $options['error_mapping'];
        }
        $validationGroups = $this->getValidationGroups($view);

        if ($view->parent) {
            return;
        }
        $this->processFormView($view, $validationGroups);

        return;

        if ($options['data_class']) {
            foreach ($this->getMappedChildren($view) as $child) {
                $constraints = $this->getFormViewConstraints($child, $metadata, $validationGroups);
                $this->addDataValidationAttribute($child, $constraints);
            }
        }
    }

    protected function processFormView(FormView $view, array $validationGroups)
    {
        $constraints = $this->extractMetadataConstraints($view);
        if ($view->children && $view->parent && !isset($view->vars['choices'])) {
            $required = isset($view->vars['required']) ? $view->vars['required'] : false;
            if (!$required) {
                $view->vars['attr']['data-validation-optional-group'] = null;
            }
        }
        foreach ($this->getMappedChildren($view) as $child) {
            $childConstraints = $this->getFormViewConstraints($child, $constraints, $validationGroups);
            $this->addDataValidationAttribute($child, $childConstraints);

            if ($child->children) {
                $this->processFormView($child, $validationGroups);
            }
            if (isset($child->vars['prototype']) && $child->vars['prototype'] instanceof FormView) {
                $this->processFormView($child->vars['prototype'], $validationGroups);
            }
        }
    }

    protected function extractMetadataConstraints(FormView $view)
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

    protected function addDataValidationAttribute(FormView $view, array $constraints)
    {
        $value = array();
        foreach ($constraints as $constraint) {
            $value[$this->getConstraintName($constraint)] = $this->getConstraintProperties($constraint);
        }
        if ($value) {
            $target = $view;
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

    protected function getConstraintName(Constraint $constraint)
    {
        $class = get_class($constraint);
        $defaultClassPrefix = 'Symfony\\Component\\Validator\\Constraints\\';
        if (0 === strpos($class, $defaultClassPrefix)) {
            return str_replace($defaultClassPrefix, '', $class);
        }
        return $class;
    }

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
     * @param FormView $view
     * @param Constraint[] $constraints
     * @param array $validationGroups
     * @return array
     */
    protected function getFormViewConstraints(FormView $view, array $constraints, array $validationGroups)
    {
        $allConstraints = array();
        if (isset($constraints[$view->vars['name']])) {
            $allConstraints = $constraints[$view->vars['name']]->constraints;
        }
        if (isset($view->vars['constraints'])) {
            $allConstraints = array_merge($allConstraints, $view->vars['constraints']);
        }
        $result = array();
        foreach ($allConstraints as $constraint) {
            if (array_intersect($validationGroups, $constraint->groups)) {
                $result[] = $constraint;
            }
        }
        return $result;
    }

    /**
     * @param FormView $view
     * @return FormView[]
     */
    protected function getMappedChildren(FormView $view)
    {
        $result = array();

        foreach ($view->children as $child) {
            if (isset($child->vars['property_path']) &&
                $child->vars['property_path'] === false) {
                continue;
            }
            if (isset($child->vars['mapped']) &&
                $child->vars['mapped'] === false) {
                continue;
            }
            $result[] = $child;
        }

        return $result;
    }

    /**
     * @param FormView $view
     * @return array
     */
    protected function getValidationGroups(FormView $view)
    {
        $result = isset($view->vars['validation_groups']) ?
            $view->vars['validation_groups'] : array('Default');

        if (is_callable($result)) {
            $result = call_user_func($result, $view);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return 'form';
    }
}
