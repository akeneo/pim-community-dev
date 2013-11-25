<?php

namespace Oro\Bundle\EntityBundle\Form\Type;

use Doctrine\ORM\EntityRepository;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use Oro\Bundle\EntityBundle\ORM\OroEntityManager;

use Oro\Bundle\EntityConfigBundle\Config\Config;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;

use Oro\Bundle\EntityConfigBundle\Entity\OptionSet;
use Oro\Bundle\EntityConfigBundle\Entity\OptionSetRelation;
use Oro\Bundle\EntityConfigBundle\Entity\Repository\OptionSetRelationRepository;

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
     * @var OroEntityManager
     */
    protected $em;

    /**
     * @var EntityRepository
     */
    protected $options;

    /**
     * @var OptionSetRelationRepository
     */
    protected $relations;

    public function __construct(ConfigManager $configManager)
    {
        $this->configManager  = $configManager;
        $this->extendProvider = $configManager->getProvider('extend');

        $this->em        = $this->configManager->getEntityManager();
        $this->options   = $this->em->getRepository(OptionSet::ENTITY_NAME);
        $this->relations = $this->em->getRepository(OptionSetRelation::ENTITY_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->config = $this->extendProvider->getConfigById($options['config_id']);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, array($this, 'preSetData'));
        $builder->addEventListener(FormEvents::PRE_SUBMIT, array($this, 'preSubmitData'));
    }

    public function preSetData(FormEvent $event)
    {
        $fieldConfigId = $event->getForm()->getConfig()->getOption('config_id');
        $extendConfig  = $this->configManager->getConfig($fieldConfigId);
        $model = $this->configManager->getConfigFieldModel(
            $fieldConfigId->getClassName(),
            $fieldConfigId->getFieldName()
        );

        if ($saved = $this->relations->findBy(['field' => $model])) {
            $data = [];
            foreach ($saved as $option) {
                $data[] = $option->getOption()->getId();
            }

            if ($extendConfig->is('set_expanded')) {
                $event->setData($data);
            } else {
                $event->setData(array_shift($data));
            }
        }
    }

    public function preSubmitData(FormEvent $event)
    {
        $fieldConfigId = $event->getForm()->getConfig()->getOption('config_id');
        $model = $this->configManager->getConfigFieldModel(
            $fieldConfigId->getClassName(),
            $fieldConfigId->getFieldName()
        );
        $saved = $this->relations->findBy(['field' => $model]);
        array_walk(
            $saved,
            function (&$item) {
                $item = $item->getOption()->getId();
            }
        );

        $data  = $event->getData();
        if (!is_array($data)) {
            $data = [$data];
        }

        /**
         * Save selected options
         */
        $toSave = array_intersect($data, $saved);
        foreach ($data as $option) {
            if (!in_array($option, $saved)) {
                $optionRelation = new OptionSetRelation();
                $optionRelation->setData(null, $model, $this->options->find($option));
                $toSave[] = $option;

                $this->em->persist($optionRelation);
            }
        }

        /**
         * Remove unselected
         */
        if ($toSave && $this->relations->count($model->getId())) {
            $toRemove = $this->relations->findByNotIn($model->getId(), implode(',', $toSave));
            foreach ($toRemove as $option) {
                $this->em->remove($option);
            }
        }

        $this->em->flush();
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
