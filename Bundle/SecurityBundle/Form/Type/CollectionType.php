<?php

namespace Oro\Bundle\SecurityBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class CollectionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['fields_config'] = $options['options']['fields_config'];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_acl_collection';
    }

    public function getParent()
    {
        return 'collection';
    }
}