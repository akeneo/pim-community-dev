<?php

namespace Oro\Bundle\EntityExtendBundle\Twig;

use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;

class ViewExtension extends \Twig_Extension
{
    /** @var  ConfigManager */
    protected $configManager;

    /** @var \Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider */
    protected $entityProvider;

    /** @var \Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider */
    protected $extendProvider;

    public function __construct(ConfigManager $configManager)
    {
        $this->configManager  = $configManager;

        $this->entityProvider = $this->configManager->getProvider('entity');
        $this->extendProvider = $this->configManager->getProvider('extend');
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('dynamic', array($this, 'relationFilter')),
        );
    }

    public function relationFilter($value)
    {
        /**
         * TODO
         *
         * relations view
         * should simplify Controllers and twig templates
         */
        $value = $value;

        return 'mock';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'relation_extension';
    }
}
