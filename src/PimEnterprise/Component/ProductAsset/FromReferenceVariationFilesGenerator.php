<?php


namespace PimEnterprise\Component\ProductAsset;


use PimEnterprise\Component\ProductAsset\Model\ReferenceInterface;

class FromReferenceVariationFilesGenerator
{
    protected $variationFileGenerator;

    public function __construct(VariationFileGeneratorInterface $variationFileGenerator)
    {
        $this->variationFileGenerator = $variationFileGenerator;
    }

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
