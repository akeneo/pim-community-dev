<?php

namespace Oro\Bundle\EntityConfigBundle\Form\Type;

use Oro\Bundle\FormBundle\Config\FormConfig;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\EntityConfigBundle\ConfigManager;
use Oro\Bundle\EntityConfigBundle\Form\EventListener\ConfigSubscriber;

class ConfigEntityType extends AbstractType
{
    /**
     * @var ConfigManager
     */
    protected $configManager;

    /**
     * @param ConfigManager $configManager
     */
    public function __construct(ConfigManager $configManager)
    {
        $this->configManager = $configManager;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $className = $options['class_name'];
        $data      = array();
        foreach ($this->configManager->getProviders() as $provider) {
            if ($provider->getConfigContainer()->getEntityFormConfig()) {
                $builder->add($provider->getScope(), new ConfigType($provider), array());
                $data[$provider->getScope()] = $provider->getConfig($className)->getValues();
            }
        }
        $builder->setData($data);

        $builder->addEventSubscriber(new ConfigSubscriber($this->configManager));
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['formConfig'] = new FormConfig;

        foreach ($this->configManager->getProviders() as $provider) {
            $provider->getConfigContainer()->getEntityFormConfig($view->vars['formConfig']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(array(
            'class_name',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_entity_config_config_entity_type';
    }
}
