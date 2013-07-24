<?php

namespace Oro\Bundle\WorkflowBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

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
        if (!$options['step'] instanceof Step) {
            throw new UnexpectedTypeException($options['step'], 'Oro\Bundle\WorkflowBundle\Model\Step');
        }

        /** @var Step $step */
        $step = $options['step'];

        /** @var Attribute $attribute */
        foreach ($step->getAttributes() as $attribute) {
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
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(array('step'));
    }
}
