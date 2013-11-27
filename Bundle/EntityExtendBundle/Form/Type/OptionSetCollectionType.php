<?php

namespace Oro\Bundle\EntityExtendBundle\Form\Type;

use Oro\Bundle\EntityConfigBundle\Config\Id\FieldConfigId;
use Oro\Bundle\EntityConfigBundle\Entity\FieldConfigModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityConfigBundle\Entity\OptionSet;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class OptionSetCollectionType extends AbstractType
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
        $this->configManager  = $configManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        //$builder->addEventListener(FormEvents::POST_SET_DATA, [$this, 'preSetData']);
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'postSubmitData']);
    }

    public function preSetData(FormEvent $event)
    {
        $form = $event->getForm()->getRoot();
        $data = $form->getData();
        if (null === $data || isset($data['extend']['set_options'])) {
            return;
        }

        /** @var FieldConfigId $fieldConfigId */
        $fieldConfigId = $event->getForm()->getConfig()->getOption('config_id');

        $configModel   = $this->configManager->getConfigFieldModel(
            $fieldConfigId->getClassName(),
            $fieldConfigId->getFieldName()
        );

        $data['extend']['set_options'] =
            $this->configManager->getEntityManager()->getRepository(OptionSet::ENTITY_NAME)
                ->findBy(['field' => $configModel->getId()], ['priority' => 'ASC']);

        $form->setData($data);
    }

    public function postSubmitData(FormEvent $event)
    {
        $form        = $event->getForm();
        $data        = $event->getData();
        /** @var FieldConfigModel $configModel */
        $configModel = $form->getRoot()->getConfig()->getOptions()['config_model'];

        if (count($data)) {
            $em           = $this->configManager->getEntityManager();
            $optionValues = $oldOptions = $configModel->getOptions()->getValues();
            $newOptions   = [];
            array_walk_recursive(
                $oldOptions,
                function (&$oldOption) {
                    $oldOption = $oldOption->getId();
                }
            );

            foreach ($data as $option) {
                if (is_array($option)) {
                    $optionSet = new OptionSet();
                    $optionSet->setField($configModel);
                    $optionSet->setData(
                        $option['id'],
                        $option['priority'],
                        $option['label'],
                        (bool)$option['default']
                    );
                } elseif (!$option->getId()) {
                    $optionSet = $option;
                    $optionSet->setField($configModel);
                } else {
                    $optionSet = $option;
                }

                if ($optionSet->getLabel() != null) {
                    $newOptions[] = $optionSet->getId();
                }
                if (!in_array($optionSet, $optionValues) && $optionSet->getLabel() != null) {
                    $em->persist($optionSet);
                }
            }

            $delOptions = array_diff($oldOptions, $newOptions);
            foreach ($delOptions as $key => $delOption) {
                $em->remove($configModel->getOptions()->getValues()[$key]);
            }

            $em->flush();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'oro_collection';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_entity_option_set_collection';
    }
}
