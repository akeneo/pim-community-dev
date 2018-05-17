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

use Akeneo\Channel\Component\Model\LocaleInterface;
use Doctrine\Common\Collections\Collection;
use Akeneo\Channel\Component\Model\ChannelInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Component\TeamworkAssistant\Model\AttributeGroupCompleteness;
use PimEnterprise\Component\TeamworkAssistant\Model\ProjectInterface;

/**
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
interface PreProcessingRepositoryInterface
{
    /**
     * Inserts data into the pre processing table.
     *
     * @param ProductInterface             $product
     * @param ChannelInterface             $channel
     * @param LocaleInterface              $locale
     * @param AttributeGroupCompleteness[] $attributeGroupCompleteness
     */
    public function addAttributeGroupCompleteness(
        ProductInterface $product,
        ChannelInterface $channel,
        LocaleInterface $locale,
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
     * Check if a product belongs to a project
     *
     * @param ProductInterface $product
     *
     * @return bool
     */
    public function belongsToAProject(ProductInterface $product);

    /**
     * Check if product attribute group completeness has to be preprocessed.
     *
     * @param ProductInterface $product
     * @param ProjectInterface $project
     *
     * @return boolean
     */
    public function isProcessableAttributeGroupCompleteness(ProductInterface $product, ProjectInterface $project);
}
