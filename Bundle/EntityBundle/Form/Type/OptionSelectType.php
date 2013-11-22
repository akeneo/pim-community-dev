<?php

namespace Oro\Bundle\EntityBundle\Form\Type;

use Oro\Bundle\EntityConfigBundle\Config\Config;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityConfigBundle\Entity\FieldConfigModel;
use Oro\Bundle\EntityConfigBundle\Entity\OptionSet;
use Symfony\Component\Form\AbstractType;

use Oro\Bundle\EntityBundle\ORM\OroEntityManager;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class OptionSelectType extends AbstractType
{
    const NAME = 'oro_option_select';

    /**
     * @var ConfigManager
     */
    protected $configManager;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var FieldConfigModel
     */
    protected $model;

    public function __construct(ConfigManager $configManager)
    {
        $this->configManager = $configManager;
        $this->extendProvider = $configManager->getProvider('extend');
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->config = $this->extendProvider->getConfigById($options['config_id']);
        $this->model  = $options['config_model'];

        $builder->addEventListener(FormEvents::PRE_SET_DATA, array($this, 'preSubmitData'));
        //$builder->addEventListener(FormEvents::PRE_SUBMIT, array($this, 'preSubmitData'));
    }

    public function preSubmitData(FormEvent $event)
    {
        $data = $event->getData();
        if (!$data) {
            $data = $event->getForm()->getParent()->getData();
        }
        $form         = $event->getForm();




    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        //$vars = ['configs' => $options['configs']];
        if ($form->getData()) {
            /*$fieldConfig = $this->entityManager->getExtendManager()->getConfigProvider()->getConfig(
                $form->getParent()->getData(),
                $form->getName()
            );*/

            /*$fieldName = $fieldConfig->get('target_field');
            $vars['attr'] = array(
                'data-entities' => json_encode(
                    array(array($fieldName => $form->getData()->{'get' . ucfirst($fieldName)}()))
                )
            );*/
        }

        $vars['choices'] = $this->model->getOptions();
        foreach ($this->model->getOptions() as $option) {
            //$vars['choices'][$option->getId()] = $option->getLabel();
        }

        $view->vars = array_replace_recursive($view->vars, $vars);

    }

    /**
     * {@inheritdoc}
     */

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(['config_model']);
        $resolver->setAllowedTypes(['config_model' => 'Oro\Bundle\EntityConfigBundle\Entity\AbstractConfigModel']);
        $resolver->setDefaults(
            [
                'empty_value' => 'Please choose option...',
                //'choices'     => $this->getChoiceList()
            ]
        );
    }

    protected function getChoiceList()
    {
        $choices = [];

        //$this->configManager->

        if ($this->model) {
            foreach ($this->model->getOptions() as $choice) {
                $choices[] = $choice;
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
        return $this::NAME;
    }
}
