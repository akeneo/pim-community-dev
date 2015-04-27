<?php

namespace PamEnterprise\Component\ProductAsset\Model;

use DamEnterprise\Component\Asset\Model\FileInterface;
use Doctrine\Common\Collections\ArrayCollection;

class ProductAsset implements ProductAssetInterface
{
    /** @var int */
    protected $id;

    /** @var string */
    protected $code;

    /** @var string */
    protected $description;

    /** @var FileInterface */
    protected $reference;

    /** @var ArrayCollection of ProductAssetVariationInterface */
    protected $variations;

    public function __construct()
    {
        $this->variations = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    public function getReference()
    {
        return $this->reference;
    }

    public function setReference(FileInterface $reference)
    {
        $this->reference = $reference;

        return $this;
    }

    public function getVariations()
    {
        return $this->variations;
    }

    public function setVariations(ArrayCollection $variations)
    {
        $this->variations = $variations;
    }

    public function addVariation(ProductAssetVariationInterface $variation)
    {
        if (!$this->variations->contains($variation)) {
            $this->variations->add($variation);
        }

        return $this;
    }

    public function removeVariation(ProductAssetVariationInterface $variation)
    {
        if ($this->variations->contains($variation)) {
            $this->variations->removeElement($variation);
        }

        return $this;
    }


    public function getSortOrder()
    {
        return 0;
    }

    public static function getLabelProperty()
    {
        return 'code';
    }

    public function __toString()
    {
        return $this->getCode();
    }
}
