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
interface PreProcessAttributeCompletenessEngineInterface
{
    /**
     * Get the attribute group completeness, it compares the product attributes filled with the attributes
     * required by the family.
     *
     * @param ProjectInterface $project
     * @param ProductInterface $product
     *
     * @return array
     */
    public function getAttributeGroupCompleteness(ProjectInterface $project, ProductInterface $product);
}
