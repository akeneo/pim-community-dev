<?php

namespace PimEnterprise\Bundle\BaseConnectorBundle\Processor;

/**
 * Attribute group accesses import processor
 * Allows to bind data into an attribute group access and validate them
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class AttributeGroupAccessProcessor extends AbstractAccessProcessor
{
    /**
     * {@inheritdoc}
     */
    protected function getMapping()
    {
        return [
            'code' => 'attributeGroup',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getSupportedPermissions()
    {
        return ['viewAttributes', 'editAttributes'];
    }
}
