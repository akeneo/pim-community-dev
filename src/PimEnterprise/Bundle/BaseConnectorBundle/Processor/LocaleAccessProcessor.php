<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\BaseConnectorBundle\Processor;

/**
 * Locale accesses import processor
 * Allows to bind data into a locale access and validate them
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
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
