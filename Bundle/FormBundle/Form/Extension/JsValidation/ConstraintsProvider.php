<?php

namespace Oro\Bundle\FormBundle\Form\Extension\JsValidation;

use Symfony\Component\Form\FormInterface;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\MetadataFactoryInterface;

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
     * @param FormInterface $form
     * @return Constraint[]
     */
    public function getFormConstraints(FormInterface $form)
    {
        $constraints = $this->getMetadataConstraints($form);

        $embeddedConstraints = $form->getConfig()->getOption('constraints');
        if ($embeddedConstraints) {
            $constraints = array_merge($constraints, $embeddedConstraints);
        }

        $validationGroups = $this->getValidationGroups($form);

        $result = array();
        foreach ($constraints as $constraint) {
            if (array_intersect($validationGroups, $constraint->groups)) {
                $result[] = $constraint;
            }
        }

        return $result;
    }

    /**
     * Gets constraints for form view based on metadata
     *
     * @param FormInterface $form
     * @return array
     */
    protected function getMetadataConstraints(FormInterface $form)
    {
        $isMapped = $form->getConfig()->getOption('mapped', true);

        if (!$form->getParent() || !$isMapped) {
            return array();
        }

        $name = $form->getName();
        $parent = $form->getParent();
        $parentKey = spl_object_hash($parent);

        if (!isset($this->metadataConstraintsCache[$parentKey])) {
            $this->metadataConstraintsCache[$parentKey] = $this->extractMetadataPropertiesConstraints($parent);
        }

        $result = array();

        if (isset($this->metadataConstraintsCache[$parentKey][$name])) {
            $result = $this->metadataConstraintsCache[$parentKey][$name]->constraints;
        }

        return $result;
    }

    /**
     * Extracts constraints based on validation metadata
     *
     * @param FormInterface $form
     * @return array
     */
    protected function extractMetadataPropertiesConstraints(FormInterface $form)
    {
        $constraints = array();
        if ($form->getConfig()->getDataClass()) {
            /** @var ClassMetadata $metadata */
            $metadata = $this->metadataFactory->getMetadataFor($form->getConfig()->getDataClass());
            $constraints = $metadata->properties;
        }
        $errorMapping = $form->getConfig()->getOption('error_mapping');
        if (!empty($constraints) && !empty($errorMapping)) {
            foreach ($errorMapping as $originalName => $mappedName) {
                if (isset($constraints[$originalName])) {
                    $constraints[$mappedName] = $constraints[$originalName];
                }
            }
        }

        return $constraints;
    }

    /**
     * Returns the validation groups of the given form.
     *
     * @param FormInterface $form
     * @return array
     */
    protected function getValidationGroups(FormInterface $form)
    {
        do {
            $groups = $form->getConfig()->getOption('validation_groups');

            if (null !== $groups) {
                return $this->resolveValidationGroups($groups, $form);
            }

            $form = $form->getParent();
        } while (null !== $form);

        return array(Constraint::DEFAULT_GROUP);
    }

    /**
     * Post-processes the validation groups option for a given form.
     *
     * @param array|callable $groups The validation groups.
     * @param FormInterface  $form   The validated form.
     *
     * @return array The validation groups.
     */
    protected function resolveValidationGroups($groups, FormInterface $form)
    {
        if (!is_string($groups) && is_callable($groups)) {
            $groups = call_user_func($groups, $form);
        }

        return (array) $groups;
    }
}
