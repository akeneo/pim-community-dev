<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ActivityManager\Job\ProjectCalculation;

use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;

/**
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
interface AddUserGroupEngineInterface
{
    /**
     * @param ProjectInterface $project
     * @param ProductInterface $product
     */
    public function addUserGroup(ProjectInterface $project, ProductInterface $product);
}
