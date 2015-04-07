<?php

namespace Pim\Bundle\EnrichBundle\Provider;

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
    protected $defaults = [
        'module'       => null,
        'parent'       => null,
        'targetZone'   => null,
        'insertAction' => 'append',
        'zones'        => []
    ];

    /**
     * @param string $code
     * @param array  $config
     */
    public function addExtension($code, array $config)
    {
        $config = $config + $this->defaults + ['code' => $code];

        $this->extensions[] = $config;
    }

    /**
     * @return array
     */
    public function getExtensions()
    {
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
