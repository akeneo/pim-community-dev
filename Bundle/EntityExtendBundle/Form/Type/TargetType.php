<?php

namespace Oro\Bundle\EntityExtendBundle\Form\Type;

use Oro\Bundle\EntityConfigBundle\Entity\FieldConfigModel;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Form\AbstractType;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;

class TargetType extends AbstractType
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

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        if (null === $this->request->get('entity')) {
            /** @var FieldConfigModel $entity */
            $entity = $this->configManager->getEntityManager()
                ->getRepository(FieldConfigModel::ENTITY_NAME)
                ->find($this->request->get('id'));

            $entityClassName = $entity->getEntity()->getClassName();
        } else {
            $entityClassName = $this->request->get('entity')->getClassName();
        }

        $options = array();

        $entities = $this->configManager->getIds('entity');
        foreach ($entities as $entity) {
            $entityName = $moduleName = '';

            if ($entity->getClassName() != $entityClassName) {

                $className = explode('\\', $entity->getClassName());
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
        }

        $resolver->setDefaults(
            array(
                'required' => true,
                'choices'  => $options,
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