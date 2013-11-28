<?php

namespace Oro\Bundle\WorkflowBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Exception\InvalidConfigurationException;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\Options;

use Oro\Bundle\WorkflowBundle\Form\EventListener\DefaultValuesListener;
use Oro\Bundle\WorkflowBundle\Form\EventListener\InitActionsListener;
use Oro\Bundle\WorkflowBundle\Form\EventListener\RequiredAttributesListener;
use Oro\Bundle\WorkflowBundle\Model\WorkflowData;
use Oro\Bundle\WorkflowBundle\Model\Attribute;
use Oro\Bundle\WorkflowBundle\Model\Workflow;
use Oro\Bundle\WorkflowBundle\Model\WorkflowRegistry;

class WorkflowAttributesType extends AbstractType
{
    const NAME = 'oro_workflow_attributes';

    /**
     * @var WorkflowRegistry
     */
    protected $workflowRegistry;

    /**
     * @var WorkflowData
     */
    protected $workflowData;

    /**
     * @var DefaultValuesListener
     */
    protected $defaultValuesListener;

    /**
     * @var InitActionsListener
     */
    protected $initActionsListener;

    /**
     * @var RequiredAttributesListener
     */
    protected $requiredAttributesListener;

    /**
     * @param WorkflowRegistry $workflowRegistry
     * @param DefaultValuesListener $defaultValuesListener
     * @param InitActionsListener $initActionsListener
     * @param RequiredAttributesListener $requiredAttributesListener
     */
    public function __construct(
        WorkflowRegistry $workflowRegistry,
        DefaultValuesListener $defaultValuesListener,
        InitActionsListener $initActionsListener,
        RequiredAttributesListener $requiredAttributesListener
    ) {
        $this->workflowRegistry = $workflowRegistry;
        $this->defaultValuesListener = $defaultValuesListener;
        $this->initActionsListener = $initActionsListener;
        $this->requiredAttributesListener = $requiredAttributesListener;
    }

    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->addEventListeners($builder, $options);
        $this->addAttributes($builder, $options);
    }

    /**
     * Adds required event listeners
     *
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    protected function addEventListeners(FormBuilderInterface $builder, array $options)
    {
        $this->defaultValuesListener->initialize(
            $options['workflow_item'],
            $options['attribute_default_values']
        );

        $builder->addEventSubscriber($this->defaultValuesListener);

        if (!empty($options['attribute_init_actions'])) {
            $this->initActionsListener->initialize(
                $options['workflow_item'],
                $options['attribute_init_actions']
            );
            $builder->addEventSubscriber($this->initActionsListener);
        }

        $this->requiredAttributesListener->initialize(array_keys($options['attribute_fields']));
        $builder->addEventSubscriber($this->requiredAttributesListener);
    }

    /**
     * Add attributes to form
     *
     * @param FormBuilderInterface $builder
     * @param array $options
     * @throws InvalidConfigurationException When attribute is not found in given Workflow
     */
    protected function addAttributes(FormBuilderInterface $builder, array $options)
    {
        /** @var Workflow $workflow */
        $workflow = $options['workflow'];

        foreach ($options['attribute_fields'] as $attributeName => $attributeOptions) {
            $attribute = $workflow->getAttributeManager()->getAttribute($attributeName);
            if (!$attribute) {
                throw new InvalidConfigurationException(
                    sprintf(
                        'Invalid reference to unknown attribute "%s" of workflow "%s".',
                        $attributeName,
                        $workflow->getName()
                    )
                );
            }
            $this->addAttributeField($builder, $attribute, $attributeOptions, $options);
        }
    }

    /**
     * Adds form type of attribute to form builder
     *
     * @param FormBuilderInterface $builder
     * @param Attribute $attribute
     * @param array $attributeOptions
     * @param array $options
     */
    protected function addAttributeField(
        FormBuilderInterface $builder,
        Attribute $attribute,
        array $attributeOptions,
        array $options
    ) {
        $attributeOptions = $this->prepareAttributeOptions($attribute, $attributeOptions, $options);
        $builder->add($attribute->getName(), $attributeOptions['form_type'], $attributeOptions['options']);
    }

    /**
     * Prepares options of attribute need to add corresponding form type
     *
     * @param Attribute $attribute
     * @param array $attributeOptions
     * @param array $options
     * @return array
     * @throws InvalidConfigurationException
     */
    protected function prepareAttributeOptions(Attribute $attribute, array $attributeOptions, array $options)
    {
        /** @var Workflow $workflow */
        $workflow = $options['workflow'];

        // ensure has form_type
        if (empty($attributeOptions['form_type'])) {
            throw new InvalidConfigurationException(
                sprintf(
                    'Parameter "form_type" must be defined for attribute "%s" in workflow "%s".',
                    $attribute->getName(),
                    $workflow->getName()
                )
            );
        }

        // updates form options
        if (!isset($attributeOptions['options'])) {
            $attributeOptions['options'] = array();
        }

        // updates form options label
        if (!isset($attributeOptions['options']['label'])) {
            $attributeOptions['options']['label'] = isset($attributeOptions['label'])
                ? $attributeOptions['label']
                : $attribute->getLabel();
        }

        if ($options['disable_attribute_fields']) {
            $attributeOptions['options']['disabled'] = true;
        }

        return $attributeOptions;
    }

    /**
     * Custom options:
     * - "attribute_fields"         - required, list of attributes form types options
     * - "workflow_item"            - optional, instance of WorkflowItem entity
     * - "workflow"                 - optional, instance of Workflow
     * - "workflow_name"            - optional, name of Workflow
     * - "disable_attribute_fields" - optional, a flag to disable all attributes fields
     *
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setOptional(
            array(
                'attribute_fields',
                'attribute_default_values',
                'attribute_init_actions',
                'workflow',
                'workflow_item',
                'workflow_name'
            )
        );

        $resolver->setDefaults(
            array(
                'data_class' => 'Oro\Bundle\WorkflowBundle\Model\WorkflowData',
                'disable_attribute_fields' => false,
                'attribute_fields' => array(),
                'attribute_default_values' => array(),
                'validation_groups' => array('Workflow')
            )
        );

        $resolver->setAllowedTypes(
            array(
                'workflow_item' => 'Oro\Bundle\WorkflowBundle\Entity\WorkflowItem',
                'workflow' => 'Oro\Bundle\WorkflowBundle\Model\Workflow',
                'attribute_fields' => 'array',
                'attribute_default_values' => 'array',
                'attribute_init_actions' => 'Oro\Bundle\WorkflowBundle\Model\Action\ActionInterface',
            )
        );

        $workflowRegistry = $this->workflowRegistry;

        $resolver->setNormalizers(
            array(
                'workflow' => function (Options $options, $workflow) use ($workflowRegistry) {
                    if (!$workflow) {
                        if (!empty($options['workflow_item'])) {
                            $workflowName = $options['workflow_item']->getWorkflowName();
                        } elseif (!empty($options['workflow_name'])) {
                            $workflowName = $options['workflow_name'];
                        } else {
                            throw new InvalidConfigurationException(
                                'One of the options must be specified: "workflow", "workflow_item", "workflow_name".'
                            );
                        }
                        $workflow = $this->workflowRegistry->getWorkflow($workflowName);
                    }
                    return $workflow;
                },
            )
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return self::NAME;
    }
}
