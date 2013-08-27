<?php

namespace Oro\Bundle\OrganizationBundle\Twig;

use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Doctrine\Common\Util\ClassUtils;

class OwnerTypeExtension extends \Twig_Extension
{
    const EXTENSION_NAME = 'oro_owner_type';

    /**
     * @var ConfigProvider
     */
    protected $configProvider;

    /**
     * @param ConfigProvider $configProvider
     */
    public function __construct(ConfigProvider $configProvider)
    {
        $this->configProvider = $configProvider;
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return array(
            'oro_get_owner_type' => new \Twig_Function_Method(
                $this,
                'getOwnerType'
            )
        );
    }

    /**
     * @param $entity
     * @return string
     */
    public function getOwnerType($entity)
    {
        $ownerClassName = ClassUtils::getRealClass(get_class($entity));
        if (!$this->configProvider->hasConfig($ownerClassName)) {
            return;
        }
        $config = $this->configProvider->getConfig($ownerClassName)->getValues();

        return $config['owner_type'];
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return self::EXTENSION_NAME;
    }
}
