<?php

namespace Oro\Bundle\EntityConfigBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

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

        $data = array();
        foreach ($this->configManager->getProviders() as $provider) {
            if ($provider->getConfigContainer()->hasEntityForm()) {
                $builder->add(
                    $provider->getScope(),
                    new ConfigType($provider->getConfigContainer()->getEntityItems()),
                    array(
                        'block_config' => (array) $provider->getConfigContainer()->getEntityFormBlockConfig()
                    )
                );
                $data[$provider->getScope()] = $provider->getConfig($className)->getValues();
            }
        }
        $builder->setData($data);

        $builder->addEventSubscriber(new ConfigSubscriber($this->configManager));
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
