<?php

namespace Oro\Bundle\EntityExtendBundle\Form\Type;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\EntityConfigBundle\Config\Config;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;

class TargetFieldType extends AbstractType
{
    /**
     * @var ConfigProvider
     */
    protected $configProvider;

    /**
     * @var string
     */
    protected $entityClass;

    public function __construct(ConfigProvider $configProvider, $entityClass)
    {
        $this->configProvider = $configProvider;
        $this->entityClass    = $entityClass;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'attr'            => array('class' => 'extend-rel-target-field'),
                'label'           => 'Target field',
                'empty_value'     => 'Please choice target field...',
                'choices'         => $this->getPropertyChoiceList(),
                'auto_initialize' => false
            )
        );
    }

    /**
     * @return array
     */
    protected function getPropertyChoiceList()
    {
        $choices = array();

        if (!$this->entityClass) {
            return $choices;
        }

        $fields = $this->configProvider->filter(
            function (Config $config) {
                return
                    $config->getId()->getFieldType() == 'string'
                    && $config->is('is_deleted', false);
            },
            $this->entityClass
        );

        $entityConfigProvider = $this->configProvider->getConfigManager()->getProvider('entity');
        foreach ($fields as $field) {
            $label = $entityConfigProvider->getConfigById($field->getId())->get('label');

            $choices[$field->getId()->getFieldName()] = $label ? : $field->getId()->getFieldName();
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
        return 'oro_entity_target_field_type';
    }
}
