<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\EnrichBundle\MassEditAction\Operation;

use Pim\Bundle\EnrichBundle\MassEditAction\Operation\Classify as BaseClassify;

/**
 * Batch operation to classify products
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class Classify extends BaseClassify
{
    /**
     * {@inheritdoc}
     *
     * We override the parent job, because the job we'll use checks if the user
     * has own right on edited products
     */
    public function getBatchJobCode()
    {
        return 'add_product_value_with_permission';
    }
}
