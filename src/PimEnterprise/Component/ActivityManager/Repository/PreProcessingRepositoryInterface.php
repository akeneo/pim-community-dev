<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ActivityManager\Repository;

use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;

/**
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
interface PreProcessingRepositoryInterface
{
    /**
     * Inserts data into the pre processing table.
     *
     * @param ProductInterface $product
     * @param ProjectInterface $project
     * @param array            $attributeGroupCompleteness
     */
    public function addAttributeGroupCompleteness(ProductInterface $product, ProjectInterface $project, array $attributeGroupCompleteness);

    /**
     * Link a product to a project
     *
     * @param ProjectInterface $project
     * @param ProductInterface $product
     */
    public function addProduct(ProjectInterface $project, ProductInterface $product);

    /**
     * Reset all pre processed completeness data
     *
     * @param ProjectInterface $project
     */
    public function reset(ProjectInterface $project);

    /**
     * Remove entries with products linked to the given project AND not linked to others projects.
     *
     * @param ProjectInterface $project
     */
    public function remove(ProjectInterface $project);
}
