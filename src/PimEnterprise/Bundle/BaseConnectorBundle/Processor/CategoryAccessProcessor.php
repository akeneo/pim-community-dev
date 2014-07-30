<?php

namespace PimEnterprise\Bundle\BaseConnectorBundle\Processor;

/**
 * Category access import processor
 * Allows to bind data into a category access and validate them
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class CategoryAccessProcessor extends AbstractAccessProcessor
{
    /**
     * {@inheritdoc}
     */
    protected function getMapping()
    {
        return [
            'code' => 'category',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getSupportedPermissions()
    {
        return ['viewProducts', 'editProducts', 'ownProducts'];
    }
}
