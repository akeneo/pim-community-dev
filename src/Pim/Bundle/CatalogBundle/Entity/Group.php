<?php

namespace Pim\Bundle\CatalogBundle\Entity;

use Akeneo\Component\Localization\Model\TranslationInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Model\GroupTypeInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductTemplateInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Group entity
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @Assert\GroupSequenceProvider
 */
class Group implements GroupInterface
{
    /** @var int $id */
    protected $id;

    /** @var string $code */
    protected $code;

    /** @var GroupTypeInterface */
    protected $type;

    /**  @var ArrayCollection */
    protected $products;

    /**  @var ArrayCollection */
    protected $axisAttributes;

    /**
     * Used locale to override Translation listener's locale
     * this is not a mapped field of entity metadata, just a simple property
     *
     * @var string
     */
    protected $locale;

    /**  @var ArrayCollection $translations */
    protected $translations;

    /**  @var ProductTemplateInterface */
    protected $productTemplate;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->products = new ArrayCollection();
        $this->translations = new ArrayCollection();
        $this->axisAttributes = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * {@inheritdoc}
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setType(GroupTypeInterface $type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * {@inheritdoc}
     */
    public function getTranslation($locale = null)
    {
        $locale = ($locale) ? $locale : $this->locale;
        if (null === $locale) {
            return null;
        }
        foreach ($this->getTranslations() as $translation) {
            if ($translation->getLocale() == $locale) {
                return $translation;
            }
        }

        $translationClass = $this->getTranslationFQCN();
        $translation = new $translationClass();
        $translation->setLocale($locale);
        $translation->setForeignKey($this);
        $this->addTranslation($translation);

        return $translation;
    }

    /**
     * {@inheritdoc}
     */
    public function addTranslation(TranslationInterface $translation)
    {
        if (!$this->translations->contains($translation)) {
            $this->translations->add($translation);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeTranslation(TranslationInterface $translation)
    {
        $this->translations->removeElement($translation);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTranslationFQCN()
    {
        return 'Pim\Bundle\CatalogBundle\Entity\GroupTranslation';
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        $translated = $this->getTranslation() ? $this->getTranslation()->getLabel() : null;

        return ($translated !== '' && $translated !== null) ? $translated : '['.$this->getCode().']';
    }

    /**
     * {@inheritdoc}
     */
    public function setLabel($label)
    {
        $this->getTranslation()->setLabel($label);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addProduct(ProductInterface $product)
    {
        if (!$this->products->contains($product)) {
            $this->products->add($product);
            $product->addGroup($this);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeProduct(ProductInterface $product)
    {
        $this->products->removeElement($product);
        $product->removeGroup($this);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * {@inheritdoc}
     */
    public function setProducts(array $products)
    {
        $this->products = new ArrayCollection($products);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addAxisAttribute(AttributeInterface $axisAttribute)
    {
        if (!$this->axisAttributes->contains($axisAttribute)) {
            $this->axisAttributes[] = $axisAttribute;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeAxisAttribute(AttributeInterface $axisAttribute)
    {
        $this->axisAttributes->removeElement($axisAttribute);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAxisAttributes()
    {
        return $this->axisAttributes;
    }

    /**
     * {@inheritdoc}
     */
    public function setAxisAttributes(array $newAxisAttributes = [])
    {
        foreach ($this->axisAttributes as $axisAttribute) {
            if (!in_array($axisAttribute, $newAxisAttributes)) {
                $this->removeAxisAttribute($axisAttribute);
            }
        }
        foreach ($newAxisAttributes as $newAxisAttribute) {
            $this->addAxisAttribute($newAxisAttribute);
        }

        return $this;
    }

    /**
     * Return the identifier-based validation group for validation of properties
     *
     * @return string[]
     */
    public function getGroupSequence()
    {
        return ['Group', strtolower($this->getType()->getCode())];
    }

    /**
     * Returns the label of the group
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getLabel();
    }

    /**
     * {@inheritdoc}
     */
    public function getReference()
    {
        return $this->code;
    }

    /**
     * {@inheritdoc}
     */
    public function getProductTemplate()
    {
        return $this->productTemplate;
    }

    /**
     * {@inheritdoc}
     */
    public function setProductTemplate(ProductTemplateInterface $productTemplate)
    {
        $this->productTemplate = $productTemplate;

        return $this;
    }
}
