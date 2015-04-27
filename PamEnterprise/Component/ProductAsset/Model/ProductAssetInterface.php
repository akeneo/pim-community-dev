<?php

namespace PamEnterprise\Component\ProductAsset\Model;

use DamEnterprise\Component\Asset\Model\FileInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Pim\Component\ReferenceData\Model\ReferenceDataInterface;

interface ProductAssetInterface extends ReferenceDataInterface
{
    /**
     * @return int
     */
    public function getId();

    /**
     * @return string
     */
    public function getCode();

    /**
     * @param string $code
     *
     * @return ProductAssetInterface
     */
    public function setCode($code);

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @param string $description
     *
     * @return ProductAssetInterface
     */
    public function setDescription($description);

    /**
     * @return FileInterface
     */
    public function getReference();

    /**
     * @param FileInterface $reference
     *
     * @return ProductAssetInterface
     */
    public function setReference(FileInterface $reference);

    /**
     * @return ArrayCollection
     */
    public function getVariations();

    /**
     * @param ArrayCollection $variations
     *
     * @return ProductAssetInterface
     */
    public function setVariations(ArrayCollection $variations);

    /**
     * @param ProductAssetVariationInterface $variation
     *
     * @return ProductAssetInterface
     */
    public function addVariation(ProductAssetVariationInterface $variation);

    /**
     * @param ProductAssetVariationInterface $variation
     *
     * @return ProductAssetInterface
     */
    public function removeVariation(ProductAssetVariationInterface $variation);
}
