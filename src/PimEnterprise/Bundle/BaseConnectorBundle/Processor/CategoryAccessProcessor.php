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
 * Category access import processor
 * Allows to bind data into a category access and validate them
 *
 * @author Romain Monceau <romain@akeneo.com>
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
