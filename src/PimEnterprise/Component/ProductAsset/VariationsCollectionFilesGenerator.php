<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ProductAsset;

use PimEnterprise\Component\ProductAsset\Exception\LockedVariationGenerationException;
use PimEnterprise\Component\ProductAsset\Model\VariationInterface;

/**
 * Generate the variation files for a collection of variations
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class VariationsCollectionFilesGenerator implements VariationsCollectionFilesGeneratorInterface
{
    /** @var VariationFileGeneratorInterface */
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
    public function generate(array $variations, $force = false)
    {
        $processedVariations = new ProcessedItemList();

        foreach ($variations as $variation) {
            if (!$variation instanceof VariationInterface) {
                throw new \InvalidArgumentException(
                    'The collection should contains only ' .
                    '"PimEnterprise\Component\ProductAsset\Model\VariationInterface"'
                );
            }

            if ($force || !$variation->isLocked()) {
                try {
                    $this->variationFileGenerator->generate($variation);
                    $processedVariations->addItem($variation, ProcessedItem::STATE_SUCCESS);
                } catch (\Exception $e) {
                    $processedVariations->addItem($variation, ProcessedItem::STATE_ERROR, null, $e);
                }
            } else {
                $processedVariations->addItem(
                    $variation,
                    ProcessedItem::STATE_SKIPPED,
                    null,
                    new LockedVariationGenerationException()
                );
            }
        }

        return $processedVariations;
    }
}
