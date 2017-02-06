<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\TeamworkAssistant\Repository;

use Doctrine\Common\Collections\Collection;
use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Component\TeamworkAssistant\Model\ProjectInterface;

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
    public function addAttributeGroupCompleteness(
        ProductInterface $product,
        ProjectInterface $project,
        array $attributeGroupCompleteness
    );

    /**
     * Link a product to a project
     *
     * @param ProjectInterface $project
     * @param ProductInterface $product
     */
    public function addProduct(ProjectInterface $project, ProductInterface $product);

    /**
     * Link a product to a category
     *
     * @param ProductInterface $product
     * @param Collection       $categories
     */
    public function link(ProductInterface $product, Collection $categories);

    /**
     * Reset all pre processed completeness data
     *
     * @param ProjectInterface $project
     */
    public function prepareProjectCalculation(ProjectInterface $project);

    /**
     * Remove entries with products linked to the given project AND not linked to others projects.
     *
     * @param ProjectInterface $project
     */
    public function remove(ProjectInterface $project);

    /**
     * TODO
     *
     * @param ProductInterface $product
     * @param ProjectInterface $project
     *
     * @return boolean
     */
    public function isPreProcessable(ProductInterface $product, ProjectInterface $project);
}
