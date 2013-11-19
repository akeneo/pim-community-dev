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
        $validationGroups = $this->getValidationGroups($view);

        if ($options['data_class']) {
            $metadata = $this->metadataFactory->getMetadataFor($options['data_class']);

            foreach ($this->getMappedChildren($view) as $child) {
                $constraints = $this->getFormViewConstraints($child, $metadata, $validationGroups);
                $this->addDataValidationAttribute($child, $constraints);
            }
        }
    }

    protected function addDataValidationAttribute(FormView $view, array $constraints)
    {
        $value = array();
        foreach ($constraints as $constraint) {
            $value[$this->getConstraintName($constraint)] = $this->getConstraintProperties($constraint);
        }
        if ($value) {
            if (!isset($view->vars['attr'])) {
                $view->vars['attr'] = array();
            }
            $view->vars['attr']['data-validation'] = json_encode($value);
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

    protected function getFormViewConstraints(FormView $view, MetadataInterface $metadata, array $validationGroups)
    {
        /** @var PropertyMetadataInterface[] $properties */
        $properties = $metadata->properties;
        $constraints = array();
        if (isset($properties[$view->vars['name']])) {
            /** @var Constraint[] $constraints */
            $constraints = $properties[$view->vars['name']]->getConstraints();
        }
        if (isset($view->vars['constraints'])) {
            $constraints = array_merge($constraints, $view->vars['constraints']);
        }
        $result = array();
        foreach ($constraints as $constraint) {
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
