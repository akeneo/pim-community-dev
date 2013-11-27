<?php

namespace Oro\Bundle\EntityBundle\Form\Type;

use Doctrine\ORM\EntityRepository;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Routing\Router;

use Oro\Bundle\EntityBundle\ORM\OroEntityManager;

use Oro\Bundle\EntityConfigBundle\Config\Config;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;

use Oro\Bundle\EntityConfigBundle\Entity\OptionSet;
use Oro\Bundle\EntityConfigBundle\Entity\OptionSetRelation;
use Oro\Bundle\EntityConfigBundle\Entity\Repository\OptionSetRelationRepository;

class OptionSelectType extends AbstractType
{
    const NAME   = 'oro_option_select';
    const PARENT = 'choice';

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

    /**
     * @param ConfigManager $configManager
     */
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
        //$builder->addEventListener(FormEvents::PRE_SUBMIT, array($this, 'preSubmitData'));
        $builder->addEventListener(FormEvents::POST_SUBMIT, array($this, 'postSubmitData'));
    }

    public function preSetData(FormEvent $event)
    {
        list($entityId, $model, $extendConfig) = $this->prepareEvent($event);

        if ($entityId && $saved = $this->relations->findByFieldId($model->getId(), $entityId)) {
            $data = [];
            foreach ($saved as $option) {
                $data[] = $option->getOption()->getId();
            }

            if ($extendConfig->is('set_expanded')) {
                $event->setData($data);
            } else {
                $event->setData(array_shift($data));
            }
        } elseif ($entityId) {
            $event->setData($extendConfig->is('set_expanded') ? [] : '');
        }
    }

    public function preSubmitData(FormEvent $event)
    {
        list($entityId, $model, $extendConfig, $fieldConfigId) = $this->prepareEvent($event);

        $saved = [];
        if ($entityId) {
            $saved = $this->relations->findByFieldId($model->getId(), $entityId);
            array_walk(
                $saved,
                function (&$item) {
                    $item = $item->getOption()->getId();
                }
            );
        }

        $data = $event->getData();
        if (empty($data)) {
            $data = [];
        }
        if (!is_array($data)) {
            $data = [$data];
        }

        //$entityId = $this->em->getUnitOfWork()->getEntityIdentifier($entityX->getEntityY());
        //$uow = $this->em->getUnitOfWork()->getEntityIdentifier();

        /**
         * Save selected options
         */
        $toSave = array_intersect($data, $saved);
        foreach ($data as $option) {
            if (!in_array($option, $saved)) {
                $optionRelation = new OptionSetRelation();
                $optionRelation->setData(null, $entityId, $model, $this->options->find($option));
                $toSave[] = $option;

                $this->em->persist($optionRelation);
            }
        }

        /**
         * Remove unselected
         */
        if ($entityId && $this->relations->count($model->getId(), $entityId)) {
            $toRemove = $this->relations->findByNotIn($model->getId(), $entityId, $toSave);
            foreach ($toRemove as $option) {
                $this->em->remove($option);
            }
        }

        $this->em->flush();
    }

    public function postSubmitData(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        $uow = $this->em->getUnitOfWork();
    }

    /**
     * @param FormEvent $event
     * @return array
     */
    protected function prepareEvent(FormEvent $event)
    {
        $formData = $event->getForm()->getRoot()->getData();
        if (!$formData) {
            return;
        }

        $entityId      = $formData->getId();
        $fieldConfigId = $event->getForm()->getConfig()->getOption('config_id');
        $extendConfig  = $this->configManager->getConfig($fieldConfigId);
        $model         = $this->configManager->getConfigFieldModel(
            $fieldConfigId->getClassName(),
            $fieldConfigId->getFieldName()
        );

        return [$entityId, $model, $extendConfig, $fieldConfigId];
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return self::PARENT;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }
}
