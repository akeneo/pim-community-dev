<?php

namespace Oro\Bundle\FormBundle\JsValidation;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use Symfony\Component\Form\FormView;

use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\MetadataFactoryInterface;
use Symfony\Component\Validator\Constraint;

class ConstraintsProvider
{
    /**
     * @var MetadataFactoryInterface
     */
    protected $metadataFactory;

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
     * Gets constraints that should be checked on form view
     *
     * @param FormView $view
     * @param array $validationGroups
     * @return Collection
     */
    public function getFormViewConstraints(FormView $view, array $validationGroups)
    {
        $constraints = $this->getMetadataConstraints($view);

        if (isset($view->vars['constraints'])) {
            $constraints = array_merge($view->vars['constraints'], $constraints);
        }

        $result = new ArrayCollection();
        foreach ($constraints as $constraint) {
            if (array_intersect($validationGroups, $constraint->groups)) {
                $result->add($constraint);
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
            /** @var ClassMetadata $metadata */
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
     * Gets constraint name based on object
     *
     * @param Constraint $constraint
     * @return mixed|string
     */
    public function getConstraintName(Constraint $constraint)
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
    public function getConstraintProperties(Constraint $constraint)
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
}
