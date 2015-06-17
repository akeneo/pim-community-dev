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

use PimEnterprise\Component\ProductAsset\Model\ReferenceInterface;

/**
 * Generate the variation files from a reference.
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
class FromReferenceVariationFilesGenerator implements FromReferenceVariationFilesGeneratorInterface
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
     * {@inheritdoc}
     */
    public function generate(ReferenceInterface $reference)
    {
        $processedVariations = new ProcessedItemList();

        foreach ($reference->getVariations() as $variation) {
            if (!$variation->isLocked()) {
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
