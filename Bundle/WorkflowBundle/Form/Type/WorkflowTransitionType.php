<?php

namespace Oro\Bundle\WorkflowBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\Options;

use Oro\Bundle\WorkflowBundle\Validator\Constraints\TransitionIsAllowed;

class WorkflowTransitionType extends AbstractType
{
    const NAME = 'oro_workflow_transition';

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritDoc}
     */
    public function getParent()
    {
        return WorkflowAttributesType::NAME;
    }

    /**
     * Custom options:
     * - "workflow_item" - required, instance of WorkflowItem entity
     * - "transition_name" - required, name of transition
     *
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(array('workflow_item', 'transition_name'));

        $resolver->setAllowedTypes(
            array(
                'transition_name' => 'string',
            )
        );

        $resolver->setNormalizers(
            array(
                'constraints' => function (Options $options, $constraints) {
                    if (!$constraints) {
                        $constraints = array();
                    }

                    $constraints[] = new TransitionIsAllowed(
                        $options['workflow_item'],
                        $options['transition_name']
                    );

                    return $constraints;
                }
            )
        );
    }
}
