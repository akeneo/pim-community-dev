<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use PimEnterprise\Bundle\WorkflowBundle\Form\DataTransformer\StatusToBooleanTransformer;

/**
 * PimEnterprise\Bundle\WorkflowBundle\Form\Type
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class PropositionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            $builder
                ->create('status', 'checkbox')
                ->addModelTransformer(new StatusToBooleanTransformer())
        );
    }

    public function getName()
    {
        return 'pimee_workflow_proposition';
    }
}
