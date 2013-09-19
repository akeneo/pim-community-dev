<?php

namespace Oro\Bundle\EntityExtendBundle\Form\Type;

use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TargetType extends AbstractType
{
    /** @var  ConfigManager */
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
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $options = array();

        $entities = $this->configManager->getIds('entity');
        foreach ($entities as $entity) {
            $entityName = $moduleName = '';
            $className  = explode('\\', $entity->getClassName());
            if (count($className) > 1) {
                foreach ($className as $i => $name) {
                    if (count($className) - 1 == $i) {
                        $entityName = $name;
                    } elseif (!in_array($name, array('Bundle', 'Entity'))) {
                        $moduleName .= $name;
                    }
                }
            }

            $options[$entity->getClassName()] = $moduleName . ':' . $entityName;
        }

        $resolver->setDefaults(
            array(
                'required' => true,
                'empty_value' => 'Please choice target entity...',
                'choices' => $options,
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'choice';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_entity_target_type';
    }
}