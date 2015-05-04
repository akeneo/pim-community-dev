<?php

namespace PamEnterprise\Component\ProductAsset\Model;

use DamEnterprise\Component\Asset\Model\FileInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use Pim\Bundle\CatalogBundle\Model\LocaleInterface;

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

    /** @var bool */
    protected $isEnabled;

    /** @var \Datetime */
    protected $endOfUseAt;

    /** @var \Datetime */
    protected $createdAt;

    /** @var \Datetime */
    protected $updatedAt;

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

    public function getVariation(ChannelInterface $channel, LocaleInterface $locale)
    {
        foreach ($this->getVariations() as $variation) {
            if ($channel === $variation->getChannel() && $locale === $variation->getLocale()) {
                return $variation;
            }
        }

        return null;
    }

    public function hasVariation(ChannelInterface $channel, LocaleInterface $locale)
    {
        return null !== $this->getVariation($channel, $locale);
    }

    public function isEnabled()
    {
        return $this->isEnabled;
    }

    public function setEnabled($isEnabled)
    {
        $this->isEnabled = $isEnabled;
    }

    public function getEndOfUseAt()
    {
        return $this->endOfUseAt;
    }

    public function setEndOfUseAt(\Datetime $endOfUseAt)
    {
        $this->endOfUseAt = $endOfUseAt;

        return $this;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\Datetime $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\Datetime $updatedAt)
    {
        $this->updatedAt = $updatedAt;

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
