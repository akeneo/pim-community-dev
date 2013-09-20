<?php

namespace Oro\Bundle\EntityExtendBundle\Form\Type;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\Request;

use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityConfigBundle\Entity\FieldConfigModel;

class TargetFieldType extends AbstractType
{
    /** @var  ConfigManager */
    protected $configManager;

    /** @var  Request */
    protected $request;

    /**
     * @param ConfigManager $configManager
     * @param Request $request
     */
    public function __construct(ConfigManager $configManager, Request $request)
    {
        $this->configManager = $configManager;
        $this->request       = $request;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) {
                $form = $event->getForm();
                $data = $event->getData();

                $targetEntityValue = $form->getParent()->get('target_entity')->getData();
                $targetField  = $form->getParent()->get('target_field');

                //$form->getParent()->remove('target_field');
                /*
                $form->getParent()->add(
                    'target_field',
                    'oro_entity_target_field_type',
                    array(
                        //'class'       => 'extend-rel-target-field',
                        'required'    => false,
                        'label'       => 'Target field',
                        'empty_value' => 'Please choice target field...',
                        'choices'     => array(
                            'label' => 'label'
                        )
                    )
                )
                ->setData($data);
                */
            }
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
        return 'oro_entity_target_field_type';
    }
}