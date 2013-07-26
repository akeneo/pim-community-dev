<?php

namespace Oro\Bundle\EntityExtendBundle\Extend\Factory;

use Oro\Bundle\EntityExtendBundle\Extend\ExtendManager;

class ConfigFactory
{
    /**
     * @var ExtendManager
     */
    protected $extendManager;

    /**
     * @param ExtendManager $extendManager
     */
    public function __construct(ExtendManager $extendManager)
    {
        $this->extendManager = $extendManager;
    }

    public function createFieldConfig($className, $data)
    {
        $values = array();

        $values['is_extend'] = true;
        $values['owner']     = ExtendManager::OWNER_CUSTOM;
        $values['state']     = ExtendManager::STATE_NEW;

        $constraint = array(
            'property'   => array(),
            'constraint' => array()
        );

        if ($data['type'] == 'string') {
            $constraint['property']['Symfony\Component\Validator\Constraints\Length'] = array('max' => 255);
        }


        if ($data['type'] == 'datetime') {
            $constraint['property']['Symfony\Component\Validator\Constraints\DateTime'] = array();
        }

        if ($data['type'] == 'date') {
            $constraint['property']['Symfony\Component\Validator\Constraints\Date'] = array();
        }

        $values['constraint'] = serialize($constraint);

        $entityConfig = $this->extendManager->getConfigProvider()->getConfig($className);
        $entityConfig->set('state', ExtendManager::STATE_NEW);
        $this->extendManager->getConfigProvider()->persist($entityConfig);

        $this->extendManager->getConfigProvider()->createFieldConfig(
            $className,
            $data['code'],
            $data['type'],
            $values,
            true
        );
    }
}
