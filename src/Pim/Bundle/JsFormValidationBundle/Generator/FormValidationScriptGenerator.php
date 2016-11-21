<?php

namespace Pim\Bundle\JsFormValidationBundle\Generator;

use APY\JsFormValidationBundle\Generator\FieldsConstraints;
use APY\JsFormValidationBundle\Generator\FormValidationScriptGenerator as BaseFormValidationScriptGenerator;
use APY\JsFormValidationBundle\Generator\GettersLibraries;
use APY\JsFormValidationBundle\Generator\PostProcessEvent;
use APY\JsFormValidationBundle\Generator\PreProcessEvent;
use APY\JsFormValidationBundle\JsfvEvents;
use Assetic\Asset\AssetCollection;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Mapping\Factory\MetadataFactoryInterface;

/**
 * Override the form validation script generator to generate an inline script
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FormValidationScriptGenerator extends BaseFormValidationScriptGenerator
{
    /**
     * @var MetadataFactoryInterface
     */
    protected $metadataFactory;

    /**
     * @param ClassMetadata[] $classesMetadata
     */
    protected $classesMetadata;

    /**
     * @param ContainerInterface       $container
     * @param MetadataFactoryInterface $metadataFactory
     */
    public function __construct(ContainerInterface $container, MetadataFactoryInterface $metadataFactory)
    {
        parent::__construct($container);
        $this->metadataFactory = $metadataFactory;
    }

    /**
     * Gets ClassMetadata of desired class with annotations and others (xml, yml, php) using metadata factory
     *
     * @param string $className
     *
     * @return ClassMetadata Returns ClassMetadata object of desired entity with annotations info
     */
    public function getClassMetadata($className)
    {
        if (!isset($this->classesMetadata[$className])) {
            $this->classesMetadata[$className] = $this->metadataFactory->getMetadataFor($className);
        }

        return $this->classesMetadata[$className];
    }

    /**
     * This method is used instead of APY\JsFormValidationBundle\Generator\FormValidationScriptGenerator::generate
     * to return inline client-side form validation javascript
     *
     * @param FormView $formView
     * @param bool     $overwrite
     *
     * @return string
     */
    public function generate(FormView $formView, $overwrite = true)
    {
        $validationBundle = $this->getValidationBundle();
        $javascriptFramework = strtolower(
            $this->container->getParameter('apy_js_form_validation.javascript_framework')
        );
        $template = $this->container->get('templating')->render(
            "{$validationBundle}:Frameworks:JsFormValidation.js.{$javascriptFramework}.twig",
            $this->generateValidationParameters($formView)
        );

        // Js compression
        if ($this->container->getParameter('apy_js_form_validation.yui_js')) {
            // Create asset and compress it
            $asset = new AssetCollection();
            $asset->setContent($template);

            return $asset->getContent();
        }

        return $template;
    }

    /**
     * This method contains most of the logic inside
     * APY\JsFormValidationBundle\Generator\FormValidationScriptGenerator::generate
     * and returns parameters to be passed to the validation template
     *
     * Until an extensibility point is provided, we have to override the entire content of the 'generate' method,
     * thus the script generation code has been copied here
     *
     * @param FormView $formView
     *
     * @return array
     */
    protected function generateValidationParameters(FormView $formView)
    {
        $formName = isset($formView->vars['name']) ? $formView->vars['name'] : 'form';

        // Initializes variables
        $fieldsConstraints = $this->createFieldsConstraints();
        $gettersLibraries = $this->createGettersLibraries($formView);
        $aConstraints = [];
        $aGetters = [];
        $dispatcher = $this->container->get('event_dispatcher');

        // Retrieves entity name from the form view
        $formViewValue = isset($formView->vars['value']) ? $formView->vars['value'] : null;
        if (is_object($formViewValue)) {
            $entityName = get_class($formViewValue);
        } elseif (!empty($formView->vars['data_class']) && class_exists($formView->vars['data_class'])) {
            $entityName = $formView->vars['data_class'];
        }

        if (isset($entityName)) {
            // Form is built on Entity
            $metadata = $this->getClassMetadata($entityName);
            $formValidationGroups = isset($formView->vars['validation_groups']) ?
                $formView->vars['validation_groups'] : ['Default'];

            if (!is_string($formValidationGroups) && is_callable($formValidationGroups)) {
                $formValidationGroups = call_user_func($formValidationGroups, $formView);
            }

            // Dispatch JsfvEvents::preProcess event
            $preProcessEvent = new PreProcessEvent($formView, $metadata);
            // @codingStandardsIgnoreStart
            $dispatcher->dispatch(JsfvEvents::preProcess, $preProcessEvent);
            // @codingStandardsIgnoreEnd

            if ($metadata->hasConstraints()) {
                foreach ($metadata->getConstraints() as $constraint) {
                    $constraintName = $this->getConstraintName($constraint);
                    if ('UniqueEntity' === $constraintName) {
                        if (is_array($constraint->fields)) {
                            //It has not been implemented yet
                        } elseif (is_string($constraint->fields)) {
                            $aConstraints[$constraint->fields][] = $constraint;
                        }
                    } elseif ('NotBlankProperties' === $constraintName) {
                        foreach ($constraint->properties as $property) {
                            $aConstraints[$property][] = $constraint;
                        }
                    }
                }
            }
        }

        /*
         * PIM-4443: we removed the original $metadata->getters implementation to avoid issues with
         * cascade validation and getter (validation of the code of the attribute group of an attribute)
         */
        if (isset($entityName)) {
            $constraintsTarget = $metadata->properties;
        } else {
            // Simple form that is built manually
            $constraintsTarget = isset($formView->vars['constraints']) ? $formView->vars['constraints'] : null;
            if (isset($constraintsTarget[0]) && !empty($constraintsTarget[0]->fields)) {
                //Get Default group ?
                $constraintsTarget = $constraintsTarget[0]->fields;
            }
        }

        $viewsWithConstraints = $this->filterFormViewsWithConstraints($formView);
        foreach ($viewsWithConstraints as $viewWithConstraints) {
            foreach ($viewWithConstraints->vars['constraints'] as $constraint) {
                $this->addFieldConstraint(
                    $viewWithConstraints,
                    $fieldsConstraints,
                    $constraint
                );
            }
        }

        if (!empty($constraintsTarget)) {
            // we look through each field of the form
            foreach ($formView->children as $formField) {
                /* @var $formField \Symfony\Component\Form\FormView */
                // Fields with property_path=false must be excluded from validation
                if (isset($formField->vars['property_path']) &&
                    $formField->vars['property_path'] === false) {
                    continue;
                }
                //Setting "property_path" to "false" is deprecated since version 2.1 and will be removed in 2.3.
                //Set "mapped" to "false" instead
                if (isset($formField->vars['mapped']) &&
                    $formField->vars['mapped'] === false) {
                    continue;
                }
                // we look for constraints for the field
                if (isset($constraintsTarget[$formField->vars['name']])) {
                    $constraintList = isset($entityName) ?
                        $constraintsTarget[$formField->vars['name']]->getConstraints() :
                        $constraintsTarget[$formField->vars['name']]->constraints;
                    //Adds entity level constraints that have been provided for this field
                    if (!empty($aConstraints[$formField->vars['name']])) {
                        $constraintList = array_merge($constraintList, $aConstraints[$formField->vars['name']]);
                    }
                    // we look through each field constraint
                    foreach ($constraintList as $constraint) {
                        $this->addFieldConstraint(
                            $formField,
                            $fieldsConstraints,
                            $constraint
                        );
                    }
                } elseif ('reference_data_name' === $formField->vars['name'] &&
                    !empty($aConstraints[$formField->vars['name']])
                ) {
                    foreach ($aConstraints[$formField->vars['name']] as $constraint) {
                        $this->addFieldConstraint(
                            $formField,
                            $fieldsConstraints,
                            $constraint
                        );
                    }
                }
            }
        }

        // Dispatch JsfvEvents::postProcess event
        $postProcessEvent = new PostProcessEvent($formView, $fieldsConstraints);
        // @codingStandardsIgnoreStart
        $dispatcher->dispatch(JsfvEvents::postProcess, $postProcessEvent);
        // @codingStandardsIgnoreEnd

        // Retrieve validation mode from configuration
        $checkModes = ['submit' => false, 'blur' => false, 'change' => false];
        foreach ($this->container->getParameter('apy_js_form_validation.check_modes') as $checkMode) {
            $checkModes[$checkMode] = true;
        }

        return [
            'formName'           => $formName,
            'fieldConstraints'   => $fieldsConstraints->getFieldsConstraints(),
            'librairyCalls'      => $fieldsConstraints->getLibraries(),
            'check_modes'        => $checkModes,
            'getterHandlers'     => $gettersLibraries->all(),
            'gettersConstraints' => $aGetters,
        ];
    }

    /**
     * Collect recursively all form views with constraints and returns them
     *
     * @param FormView $target
     *
     * @return FormView[]
     */
    protected function filterFormViewsWithConstraints(FormView $target)
    {
        $result = [];
        if (!empty($target->vars['constraints'])) {
            $result[] = $target;
        }
        foreach ($target->children as $child) {
            $result = array_merge($result, $this->filterFormViewsWithConstraints($child));
        }

        return $result;
    }

    /**
     * @param FormView          $formType
     * @param FieldsConstraints $fieldsConstraints
     * @param Constraint        $constraint
     */
    protected function addFieldConstraint(
        FormView $formType,
        FieldsConstraints $fieldsConstraints,
        Constraint $constraint
    ) {
        $constraintName = $this->getConstraintName($constraint);
        $constraintProperties = get_object_vars($constraint);

        // Groups are no longer needed
        unset($constraintProperties['groups']);

        if (!$fieldsConstraints->hasLibrary($constraintName)) {
            $templating = $this->container->get('templating');
            $library = "APYJsFormValidationBundle:Constraints:{$constraintName}Validator.js.twig";
            if ($templating->exists($library)) {
                $fieldsConstraints->addLibrary($constraintName, $library);
            } else {
                return;
            }
        }

        $constraintParameters = [];
        $identifierField = $this->getParameter('identifier_field');
        //We need to know entity class for the field which is applied by UniqueEntity constraint
        if ($constraintName == 'UniqueEntity'
            && !empty($formType->parent)
            && !empty($formType->children[$identifierField])
        ) {
            $entity = isset($formType->parent->vars['value']) ?
                $formType->parent->vars['value'] : null;
            $constraintParameters += [
                'entity:' . json_encode(get_class($entity)),
                'identifier_field_id:' . json_encode($formType->children[$identifierField]->vars['id']),
            ];
        }
        foreach ($constraintProperties as $variable => $value) {
            if (is_array($value)) {
                $value = json_encode($value);
            } else {
                // regex
                if (stristr('pattern', $variable) === false) {
                    $value = json_encode($value);
                }
            }

            $constraintParameters[] = "$variable:$value";
        }

        $fieldsConstraints->addFieldConstraint(
            $formType->vars['id'],
            [
                'name'       => $constraintName,
                'parameters' => '{' . implode(', ', $constraintParameters) . '}'
            ]
        );
    }

    /**
     * @param FormView $formView
     *
     * @return GettersLibraries
     */
    protected function createGettersLibraries(FormView $formView)
    {
        return new GettersLibraries($this->container, $formView);
    }

    /**
     * @return FieldsConstraints
     */
    protected function createFieldsConstraints()
    {
        return new FieldsConstraints();
    }

    /**
     * Get constraint name by constraint.
     *
     * @param object $constraint
     *
     * @return string
     */
    protected function getConstraintName($constraint)
    {
        $className = get_class($constraint);
        $classParts = explode(chr(92), $className);

        return end($classParts);
    }
}
