<?php

namespace Pim\Bundle\JsFormValidationBundle\Generator;

use Symfony\Component\Form\FormView;
use Assetic\Filter\Yui\JsCompressorFilter;
use Assetic\Asset\AssetCollection;
use APY\JsFormValidationBundle\JsfvEvents;
use APY\JsFormValidationBundle\Generator\PreProcessEvent;
use APY\JsFormValidationBundle\Generator\PostProcessEvent;
use APY\JsFormValidationBundle\Generator\FieldsConstraints;
use APY\JsFormValidationBundle\Generator\GettersLibraries;
use Oro\Bundle\JsFormValidationBundle\Generator\FormValidationScriptGenerator as OroFormValidationScriptGenerator;

/**
 * Override the form validation script generator to generate an inline script
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FormValidationScriptGenerator extends OroFormValidationScriptGenerator
{
    /**
     * This method is used instead of APY\JsFormValidationBundle\Generator\FormValidationScriptGenerator::generate
     * to return inline client-side form validation javascript
     *
     * @param FormView $formView
     * @param boolean  $overwrite
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

            $yui = new JsCompressorFilter(
                $this->container->getParameter('assetic.filter.yui_js.jar'),
                $this->container->getParameter('assetic.java.bin')
            );
            $yui->filterDump($asset);

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
        $checkModes = array('submit' => false, 'blur' => false, 'change' => false);
        foreach ($this->container->getParameter('apy_js_form_validation.check_modes') as $checkMode) {
            $checkModes[$checkMode] = true;
        }

        return array(
            'formName'           => $formName,
            'fieldConstraints'   => $fieldsConstraints->getFieldsConstraints(),
            'librairyCalls'      => $fieldsConstraints->getLibraries(),
            'check_modes'        => $checkModes,
            'getterHandlers'     => $gettersLibraries->all(),
            'gettersConstraints' => $aGetters,
        );
    }
}
