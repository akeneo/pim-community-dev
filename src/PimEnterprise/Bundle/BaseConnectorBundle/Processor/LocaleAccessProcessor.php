<?php

namespace PimEnterprise\Bundle\BaseConnectorBundle\Processor;

/**
 * Locale accesses import processor
 * Allows to bind data into a locale access and validate them
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class LocaleAccessProcessor extends AbstractAccessProcessor
{
    /**
     * {@inheritdoc}
     */
    protected function getMapping()
    {
        return [
            'code' => 'locale',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getSupportedPermissions()
    {
        return ['viewProducts', 'editProducts'];
    }
}
