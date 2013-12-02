<?php

namespace Oro\Bundle\EntityExtendBundle\Form\Type;

use Symfony\Component\DependencyInjection\Container;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\EntityConfigBundle\Config\Id\EntityConfigId;
use Oro\Bundle\EntityConfigBundle\Config\Id\FieldConfigId;

use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\EntityExtendBundle\Extend\ExtendManager;

class TargetType extends AbstractType
{
    /**
     * @var ConfigProvider
     */
    protected $configProvider;

    /**
     * @var FieldConfigId
     */
    protected $configId;

    public function __construct(ConfigProvider $configProvider, $configId)
    {
        $this->configProvider = $configProvider;
        $this->configId = $configId;
        $this->targetEntity = $this->configProvider->getConfigById($this->configId)->get('target_entity');
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, array($this, 'preSetData'));
    }

    public function preSetData(FormEvent $event)
    {
        $event->setData($this->targetEntity);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'attr'        => array(
                    'class' => 'extend-rel-target-name'
                ),
                'label'       => 'Target entity',
                'empty_value' => $this->targetEntity ? false : 'Please choice target entity...',
                'read_only'   => (bool) $this->targetEntity,
                'choices'     => $this->getEntityChoiceList(
                    $this->configId->getClassName(),
                    $this->configId->getFieldType()
                )
            )
        );
    }

    protected function getEntityChoiceList($entityClassName, $relationType)
    {
        $configManager = $this->configProvider->getConfigManager();
        $choices       = array();

        if ($this->targetEntity) {
            $entityIds = array($this->configProvider->getId($this->targetEntity));
        } else {
            $entityIds = $configManager->getIds('extend');
        }

        if (in_array($relationType, array('oneToMany', 'manyToMany'))) {
            $entityIds = array_filter(
                $entityIds,
                function (EntityConfigId $configId) use ($configManager) {
                    $config = $configManager->getConfig($configId);

                    return $config->is('is_extend');
                }
            );
        }

        $entityIds = array_filter(
            $entityIds,
            function (EntityConfigId $configId) use ($configManager) {
                $config = $configManager->getConfig($configId);

                return $config->is('is_extend', false) || !$config->is('state', ExtendManager::STATE_NEW);
            }
        );

        foreach ($entityIds as $entityId) {
            $entityName = $moduleName = '';
            if ($entityId->getClassName() != $entityClassName) {
                $className  = explode('\\', $entityId->getClassName());
                if (count($className) > 1) {
                    foreach ($className as $i => $name) {
                        if (count($className) - 1 == $i) {
                            $entityName = $name;
                        } elseif (!in_array($name, array('Bundle', 'Entity'))) {
                            $moduleName .= $name;
                        }
                    }
                }

                $choices[$entityId->getClassName()] = $moduleName . ':' . $entityName;
            }
        }

        return $choices;
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
