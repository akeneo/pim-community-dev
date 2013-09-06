<?php

namespace Oro\Bundle\ConfigBundle\Form\Type;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\ConfigBundle\Form\EventListener\ConfigSubscriber;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class FormType extends AbstractType
{
    /** @var  ConfigManager */
    protected $manager;

    public function __construct(ConfigManager $manager)
    {
        $this->manager = $manager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->addEventSubscriber(new ConfigSubscriber($this->manager));
    }


    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_config_form_type';
    }
}
