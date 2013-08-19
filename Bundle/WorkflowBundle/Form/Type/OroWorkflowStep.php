<?php

namespace Oro\Bundle\WorkflowBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\Options;
use Doctrine\Common\Collections\Collection;

use Oro\Bundle\WorkflowBundle\Model\Step;
use Oro\Bundle\WorkflowBundle\Model\Attribute;

class OroWorkflowStep extends AbstractType
{
    const NAME = 'oro_workflow_step';

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     * @throws UnexpectedTypeException
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // TODO Refactor this method to use form_options from step
        /** @var Attribute[]|Collection $attributes */
        $attributes = $options['attributes'];
        foreach ($attributes as $attribute) {
            $builder->add(
                $attribute->getName(),
                $attribute->getFormTypeName(),
                $this->getAttributeFormOptions($attribute)
            );
        }
    }

    /**
     * @param Attribute $attribute
     * @return array
     */
    protected function getAttributeFormOptions(Attribute $attribute)
    {
        $formOptions = $attribute->getOption('form_options');
        $formOptions = $formOptions ? $formOptions : array();
        $formOptions['label'] = $attribute->getLabel();

        return $formOptions;
    }

    /**
     * Custom options:
     * - "step" - required, must be instance of Step entity
     * - "attributes" - not required, by default is extracted from step,
     *                  otherwise must be array or Collection of Attribute entities
     *
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(array('step'));

        $resolver->setDefaults(
            array(
                'data_class' => 'Oro\Bundle\WorkflowBundle\Model\WorkflowData',
                'attributes' => array(),
            )
        );

        $resolver->setNormalizers(
            array(
                'attributes' => function (Options $options, $attributes) {
                    if (!empty($attributes)) {
                        if (!is_array($attributes) && !$attributes instanceof Collection) {
                            throw new UnexpectedTypeException($attributes, 'array or Collection');
                        }

                        foreach ($attributes as $attribute) {
                            if (!$attribute instanceof Attribute) {
                                throw new UnexpectedTypeException(
                                    $attribute,
                                    'Oro\Bundle\WorkflowBundle\Model\Attribute'
                                );
                            }
                        }

                        return $attributes;
                    }

                    $step = $options->get('step');
                    if (!$step instanceof Step) {
                        throw new UnexpectedTypeException($step, 'Oro\Bundle\WorkflowBundle\Model\Step');
                    }

                    return $step->getAttributes();
                }
            )
        );
    }
}
