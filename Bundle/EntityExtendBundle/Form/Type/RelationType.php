<?php

namespace Oro\Bundle\EntityExtendBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\ConfigBundle\Entity\Config;

use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;

class RelationType extends AbstractType
{
    /**
     * @var ConfigProvider
     */
    protected $configProvider;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var FormFactory
     */
    protected $formFactory;

    public function __construct(ConfigProvider $configProvider)
    {
        $this->configProvider = $configProvider;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->config = $this->configProvider->getConfigById($options['config_id']);
        $this->formFactory = $builder->getFormFactory();

        $builder->add('target_entity', new TargetType($this->configProvider, $options['config_id']));

        $builder->addEventListener(FormEvents::PRE_SET_DATA, array($this, 'preSubmitData'));
        $builder->addEventListener(FormEvents::PRE_SUBMIT, array($this, 'preSubmitData'));
    }

    public function preSubmitData(FormEvent $event)
    {
        $data = $event->getData();
        if (!$data) {
            $data = $event->getForm()->getParent()->getData();
        }
        $form         = $event->getForm();
        $targetEntity = null;

        if (isset($data['target_entity'])) {
            $targetEntity = $data['target_entity'];
        }

        $relationType = $this->config->getId()->getFieldType();
        if ($relationType == 'manyToOne') {
            //target_field
            $targetField = null;
            if (isset($data['target_field'])) {
                $targetField = $data['target_field'];
            }
            $form->add(
                $this->formFactory->createNamed(
                    'target_field',
                    new TargetFieldType($this->configProvider, $targetEntity),
                    $targetField
                )
            );
        } else {
            //target_grid
            $targetGrid = null;
            if (isset($data['target_grid'])) {
                $targetGrid = $data['target_grid'];
            }
            $form->add(
                $this->formFactory->createNamed(
                    'target_grid',
                    new TargetFieldType($this->configProvider, $targetEntity),
                    $targetGrid,
                    [
                        'multiple' => true,
                        'label'    => 'Related entity data fields'
                    ]
                )
            );

            //target_title
            $targetTitle = null;
            if (isset($data['target_title'])) {
                $targetTitle = $data['target_title'];
            }
            $form->add(
                $this->formFactory->createNamed(
                    'target_title',
                    new TargetFieldType($this->configProvider, $targetEntity),
                    $targetTitle,
                    [
                        'multiple' => true,
                        'label'    => 'Related entity info title'
                    ]
                )
            );

            //target_detailed
            $targetDetailed = null;
            if (isset($data['target_detailed'])) {
                $targetDetailed = $data['target_detailed'];
            }
            $form->add(
                $this->formFactory->createNamed(
                    'target_detailed',
                    new TargetFieldType($this->configProvider, $targetEntity),
                    $targetDetailed,
                    [
                        'multiple' => true,
                        'label'    => 'Related entity detailed'
                    ]
                )
            );
        }

        if ($event->getName() == FormEvents::PRE_SUBMIT) {
            $event->getForm()->getParent()->setData(array_merge($event->getForm()->getParent()->getData(), $data));
        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'mapped' => false,
                'label'  => false
            )
        );
    }

    public function getName()
    {
        return 'oro_entity_relation_type';
    }
}
