<?php

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ProductAsset;

use PimEnterprise\Component\ProductAsset\Model\VariationInterface;

/**
 * Generate the variation files for a collection of variations
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class VariationsCollectionFilesGenerator implements VariationsCollectionFilesGeneratorInterface
{
    protected $variationFileGenerator;

    /**
     * @param VariationFileGeneratorInterface $variationFileGenerator
     */
    public function __construct(VariationFileGeneratorInterface $variationFileGenerator)
    {
        $this->variationFileGenerator = $variationFileGenerator;
    }

    /**
     * @param VariationInterface[] $variations
     *
     * @return ProcessedItemList
     */
    public function generate(array $variations)
    {
        $processedVariations = new ProcessedItemList();

        foreach ($variations as $variation) {
            // TODO check instance of VariationInterface
//            if (!$variation->isLocked()) {
            if (true) {
                try {
                    $this->variationFileGenerator->generate($variation);
                    $processedVariations->addItem($variation, ProcessedItem::STATE_SUCCESS);
                } catch (\Exception $e) {
                    $processedVariations->addItem($variation, ProcessedItem::STATE_ERROR, $e->getMessage());
                }
            } else {
                $processedVariations->addItem($variation, ProcessedItem::STATE_SKIPPED, 'The variation is locked');
            }
        }

        return $processedVariations;
    }
}
