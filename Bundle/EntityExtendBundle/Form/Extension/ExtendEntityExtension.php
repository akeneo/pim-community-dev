<?php

namespace Oro\Bundle\EntityExtendBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;

use Oro\Bundle\EntityExtendBundle\Extend\ExtendManager;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\EntityBundle\Form\Type\CustomEntityType;

class ExtendEntityExtension extends AbstractTypeExtension
{
    /**
     * @var ExtendManager
     */
    protected $extendManager;

    /**
     * @var ConfigManager
     */
    protected $configManager;

    /**
     * @param ExtendManager $extendManager
     * @param ConfigManager $configManager
     */
    public function __construct(ExtendManager $extendManager, ConfigManager $configManager)
    {
        $this->extendManager = $extendManager;
        $this->configManager = $configManager;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($builder->getForm()->getName() == CustomEntityType::NAME) {
            return;
        }

        $className = !empty($options['data_class']) ? $options['data_class'] : null;
        if (!$className) {
            return;
        }

        if (!$this->extendManager->getConfigProvider()->hasConfig($className)) {
            return;
        }

        if (!$this->hasActiveFields($className)) {
            return;
        }

        $builder->add(
            'additional',
            CustomEntityType::NAME,
            array(
                'inherit_data' => true,
                'class_name' => $className
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return 'form';
    }

    /**
     * @param string $className
     * @return bool
     */
    protected function hasActiveFields($className)
    {
        // TODO: Convert this method to separate helper service and reuse it in CustomEntityType,
        // TODO: should be done in scope of https://magecore.atlassian.net/browse/BAP-1721
        /** @var ConfigProvider $extendConfigProvider */
        $extendConfigProvider = $this->configManager->getProvider('extend');
        /** @var ConfigProvider $formConfigProvider */
        $formConfigProvider = $this->configManager->getProvider('form');

        $formConfigs = $formConfigProvider->getConfigs($className);

        // TODO: refactor ConfigIdInterface to allow extracting of field name,
        // TODO: should be done in scope https://magecore.atlassian.net/browse/BAP-1722
        foreach ($formConfigs as $formConfig) {
            $extendConfig = $extendConfigProvider->getConfig($className, $formConfig->getId()->getFieldName());
            if ($formConfig->get('is_enabled')
                && !$extendConfig->is('is_deleted')
                && $extendConfig->is('owner', ExtendManager::OWNER_CUSTOM)
                && !in_array($formConfig->getId()->getFieldType(), array('ref-one', 'ref-many'))
            ) {
                return true;
            }
        }

        return false;
    }
}
