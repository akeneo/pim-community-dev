<?php

namespace Oro\Bundle\JsFormValidationBundle\Generator;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\MetadataFactoryInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Form\FormView;
use Symfony\Component\Validator\Constraint;
use Assetic\Asset\AssetCollection;
use Assetic\Filter\Yui\JsCompressorFilter;

use APY\JsFormValidationBundle\Generator\PostProcessEvent;
use APY\JsFormValidationBundle\JsfvEvents;
use APY\JsFormValidationBundle\Generator\PreProcessEvent;
use APY\JsFormValidationBundle\Generator\FieldsConstraints;
use APY\JsFormValidationBundle\Generator\FormValidationScriptGenerator as BaseFormValidationScriptGenerator;

/**
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
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
     * @param ContainerInterface $container
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
     * Generates client-side javascript that validates form
     *
     * @param FormView $formView
     * @param boolean $overwrite
     * @throws  \RuntimeException
     */
    public function generate(FormView $formView, $overwrite = false)
    {
        // Prepare output file
        $scriptPath = $this->container->getParameter('apy_js_form_validation.script_directory');
        $scriptRealPath = $this->container->getParameter('assetic.write_to').'/'.$scriptPath;
        $formName = isset($formView->vars['name']) ? $formView->vars['name'] : 'form';
        $scriptFile = strtolower($this->container->get('request')->get('_route')) . "_" . strtolower($formName) . ".js";

        if ($overwrite || false === file_exists($scriptRealPath . $scriptFile)) {
            $this->generateFile($formView, $scriptRealPath, $scriptFile);
        }

        return $this->container->get('templating.helper.assets')->getUrl($scriptPath.$scriptFile);
    }

    /**
     * This method is copied from APY\JsFormValidationBundle\Generator\FormValidationScriptGenerator::generate
     * because there was a requirement to replace GettersLibraries with custom implementation
     *
     * TODO This method should be refactored and covered with tests (BAP-859).
     *
     * @param FormView $formView
     * @param string $scriptRealPath
     * @param string $scriptFile
     * @throws \RuntimeException
     * @SuppressWarnings(PHPMD.ConstantNamingConventions)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function generateFile(FormView $formView, $scriptRealPath, $scriptFile)
    {
        $formName = isset($formView->vars['name']) ? $formView->vars['name'] : 'form';

        // Initializes variables
        $fieldsConstraints = $this->createFieldsConstraints();
        $gettersLibraries = $this->createGettersLibraries($formView);
        $aConstraints = array();
        $aGetters = array();
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
                $formView->vars['validation_groups'] : array('Default');

            if (!is_string($formValidationGroups) && is_callable($formValidationGroups)) {
                $formValidationGroups = call_user_func($formValidationGroups, $formView);
            }

            // Dispatch JsfvEvents::preProcess event
            $preProcessEvent = new PreProcessEvent($formView, $metadata);
            // @codingStandardsIgnoreStart
            $dispatcher->dispatch(JsfvEvents::preProcess, $preProcessEvent);
            // @codingStandardsIgnoreEnd

            if (!empty($metadata->constraints)) {
                foreach ($metadata->constraints as $constraint) {
                    $constraintName = $this->getConstraintName($constraint);
                    if ($constraintName == 'UniqueEntity') {
                        if (is_array($constraint->fields)) {
                            //It has not been implemented yet
                        } elseif (is_string($constraint->fields)) {
                            if (!isset($aConstraints[$constraint->fields])) {
                                $aConstraints[$constraint->fields] = array();
                            }
                            $aConstraints[$constraint->fields][] = $constraint;
                        }
                    }
                }
            }

            $errorMapping = isset($formView->vars['error_mapping']) ? $formView->vars['error_mapping'] : null;
            if (!empty($metadata->getters)) {
                foreach ($metadata->getters as $getterMetadata) {
                    /* @var $getterMetadata \Symfony\Component\Validator\Mapping\GetterMetadata  */
                    if (!empty($getterMetadata->constraints)) {
                        if ($gettersLibraries->findLibrary($getterMetadata) === null) {
                            // You have to provide getter templates in the following location
                            // {EntityBundle}/Resources/views/Getters/{EntityName}.{GetterMethod}.js.twig
                            // or all templates in one place:
                            // app/Resources/APYJsFormValidationBundle/views/Getters/{EntityName}.{GetterMethod}.js.twig
                            continue;
                        }
                        foreach ($getterMetadata->constraints as $constraint) {
                            /* @var $constraint \Symfony\Component\Validator\Validator */
                            $getterName = $getterMetadata->getName();
                            $jsHandlerCallback = $gettersLibraries->getKey($getterMetadata, '_');
                            $constraintName = $this->getConstraintName($constraint);
                            $constraintProperties = get_object_vars($constraint);
                            $exist = array_intersect($formValidationGroups, $constraintProperties['groups']);
                            if (!empty($exist)) {
                                if (!$gettersLibraries->has($getterMetadata)) {
                                    $gettersLibraries->add($getterMetadata);
                                }
                                if (!$fieldsConstraints->hasLibrary($constraintName)) {
                                    $library =
                                        "APYJsFormValidationBundle:Constraints:{$constraintName}Validator.js.twig";
                                    $fieldsConstraints->addLibrary($constraintName, $library);
                                }
                                if (!empty($errorMapping[$getterName]) && is_string($errorMapping[$getterName])) {
                                    $fieldName = $errorMapping[$getterName];
                                    //'type' property is set in RepeatedTypeExtension class
                                    if (!empty($formView->children[$fieldName]) &&
                                        isset($formView->children[$fieldName]->vars['type']) &&
                                        $formView->children[$fieldName]->vars['type'] == 'repeated') {
                                        $repeatedNames = array_keys($formView->children[$fieldName]->vars['value']);
                                        //Listen first repeated element
                                        $fieldId =
                                            $formView->children[$fieldName]->vars['id'] . "_" . $repeatedNames[0];
                                    } else {
                                        $fieldId = $formView->children[$fieldName]->vars['id'];
                                    }
                                } else {
                                    $fieldId = '.';
                                }
                                if (!isset($aGetters[$fieldId][$jsHandlerCallback])) {
                                    $aGetters[$fieldId][$jsHandlerCallback] = array();
                                }

                                unset($constraintProperties['groups']);

                                $aGetters[$fieldId][$jsHandlerCallback][] = array(
                                    'name'       => $constraintName,
                                    'parameters' => json_encode($constraintProperties),
                                );
                            }
                        }
                    }
                }
            }
        }

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

        $formViewsWithConstraints = $this->filterFormViewsWithConstraints($formView);
        foreach ($formViewsWithConstraints as $formViewsWithConstraint) {
            foreach ($formViewsWithConstraint->vars['constraints'] as $constraint) {
                $this->addFieldConstraint(
                    $formViewsWithConstraint,
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
                }
            }
        }

        // Dispatch JsfvEvents::postProcess event
        $postProcessEvent = new PostProcessEvent($formView, $fieldsConstraints);
        // @codingStandardsIgnoreStart
        $dispatcher->dispatch(JsfvEvents::postProcess, $postProcessEvent);
        // @codingStandardsIgnoreEnd

        // Retrieve validation mode from configuration
        $check_modes = array('submit' => false, 'blur' => false, 'change' => false);
        foreach ($this->container->getParameter('apy_js_form_validation.check_modes') as $check_mode) {
            $check_modes[$check_mode] = true;
        }

        // Render the validation script
        $validation_bundle = $this->getValidationBundle();
        $javascript_framework = strtolower(
            $this->container->getParameter('apy_js_form_validation.javascript_framework')
        );
        $template = $this->container->get('templating')->render(
            "{$validation_bundle}:Frameworks:JsFormValidation.js.{$javascript_framework}.twig",
            array(
                'formName'           => $formName,
                'fieldConstraints'   => $fieldsConstraints->getFieldsConstraints(),
                'librairyCalls'      => $fieldsConstraints->getLibraries(),
                'check_modes'        => $check_modes,
                'getterHandlers'     => $gettersLibraries->all(),
                'gettersConstraints' => $aGetters,
            )
        );

        // Create asset and compress it
        $asset = new AssetCollection();
        $asset->setContent($template);
        $asset->setTargetPath($scriptRealPath . $scriptFile);

        // Js compression
        if ($this->container->getParameter('apy_js_form_validation.yui_js')) {
            $yui = new JsCompressorFilter(
                $this->container->getParameter('assetic.filter.yui_js.jar'),
                $this->container->getParameter('assetic.java.bin')
            );
            $yui->filterDump($asset);
        }

        $this->container->get('filesystem')->mkdir($scriptRealPath);

        if (false === @file_put_contents($asset->getTargetPath(), $asset->getContent())) {
            throw new \RuntimeException('Unable to write file '.$asset->getTargetPath());
        }
    }

    /**
     * Collect recursively all form views with constraints and returns them
     *
     * @param FormView $target
     * @return FormView[]
     */
    protected function filterFormViewsWithConstraints(FormView $target)
    {
        $result = array();
        if (!empty($target->vars['constraints'])) {
            $result[] = $target;
        }
        foreach ($target->children as $child) {
            $result = array_merge($result, $this->filterFormViewsWithConstraints($child));
        }
        return $result;
    }

    /**
     * @param FormView $formType
     * @param FieldsConstraints $fieldsConstraints
     * @param Constraint $constraint
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

        $constraintParameters = array();
        $identifierField = $this->getParameter('identifier_field');
        //We need to know entity class for the field which is applied by UniqueEntity constraint
        if ($constraintName == 'UniqueEntity'
            && !empty($formType->parent)
            && !empty($formType->children[$identifierField])
        ) {
            $entity = isset($formType->parent->vars['value']) ?
                $formType->parent->vars['value'] : null;
            $constraintParameters += array(
                'entity:' . json_encode(get_class($entity)),
                'identifier_field_id:' . json_encode($formType->children[$identifierField]->vars['id']),
            );
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
            array(
                'name'       => $constraintName,
                'parameters' => '{' . join(', ', $constraintParameters) . '}'
            )
        );
    }

    /**
     * @param FormView $formView
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
     * @return string
     */
    protected function getConstraintName($constraint)
    {
        $className = get_class($constraint);
        $classParts = explode(chr(92), $className);
        return end($classParts);
    }
}
