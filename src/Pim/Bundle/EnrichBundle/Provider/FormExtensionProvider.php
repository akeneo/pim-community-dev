<?php

namespace Pim\Bundle\EnrichBundle\Provider;

use Oro\Bundle\SecurityBundle\SecurityFacade;

/**
 * Form extension provider
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FormExtensionProvider
{
    /** @var array */
    protected $extensions = [];

    /** @var array */
    protected $attributeFields = [];

    /** @var array */
    protected $filters = [];

    /** @var SecurityFacade */
    protected $securityFacade;

    /**
     * @param SecurityFacade $securityFacade
     */
    public function __construct(SecurityFacade $securityFacade)
    {
        $this->securityFacade = $securityFacade;
    }

    /** @var array */
    protected $defaults = [
        'module'        => null,
        'parent'        => null,
        'targetZone'    => 'self',
        'zones'         => [],
        'aclResourceId' => null,
        'config'        => []
    ];

    /**
     * @param string $code
     * @param array  $config
     */
    public function addExtension($code, array $config)
    {
        $config = $config + $this->defaults + ['code' => $code];

        if (!isset($config['position'])) {
            $config['position'] = 100;
        }

        $this->extensions[] = $config;
    }

    /**
     * @return array
     */
    public function getFilteredExtensions()
    {
        $extensions = $this->getExtensions();
        $securityFacade = $this->securityFacade;

        return array_filter(
            $extensions,
            function ($extension) use ($securityFacade) {
                $acl = $extension['aclResourceId'];

                return null === $acl || $securityFacade->isGranted($acl);
            }
        );
    }
    /**
     * @return array
     */
    public function getExtensions()
    {
        usort($this->extensions, function ($extension1, $extension2) {
            return (int) $extension1['position'] - (int) $extension2['position'];
        });

        return $this->extensions;
    }

    /**
     * @param string $attributeType
     * @param string $module
     */
    public function addAttributeField($attributeType, $module)
    {
        $this->attributeFields[$attributeType] = $module;
    }

    /**
     * @return array
     */
    public function getAttributeFields()
    {
        return $this->attributeFields;
    }
}
