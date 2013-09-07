<?php

namespace Oro\Bundle\ConfigBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Oro\Bundle\ConfigBundle\Form\EventListener\ConfigSubscriber;

class FormType extends AbstractType
{
    /** @var  ConfigSubscriber */
    protected $subscriber;

    public function __construct(ConfigSubscriber $subscriber)
    {
        $this->subscriber = $subscriber;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->addEventSubscriber($this->subscriber);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_config_form_type';
    }
}
