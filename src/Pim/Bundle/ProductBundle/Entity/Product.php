<?php

namespace Pim\Bundle\ProductBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityFlexible;
use Pim\Bundle\ProductBundle\Entity\Locale;
use Pim\Bundle\ProductBundle\Entity\ProductAttribute;
use Pim\Bundle\ProductBundle\Model\ProductInterface;
use Pim\Bundle\ProductBundle\Exception\MissingIdentifierException;
use Pim\Bundle\VersioningBundle\Entity\VersionableInterface;
use Pim\Bundle\ImportExportBundle\Normalizer\ProductNormalizer;

/**
 * Flexible product
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Table(name="pim_product")
 * @ORM\Entity(repositoryClass="Pim\Bundle\ProductBundle\Entity\Repository\ProductRepository")
 */
class Product extends AbstractEntityFlexible implements ProductInterface, VersionableInterface
{
    /**
     * @var integer $version
     *
     * @ORM\Column(name="version", type="integer")
     * @ORM\Version
     */
    protected $version;

    /**
     * @var ArrayCollection $values
     *
     * @ORM\OneToMany(
     *     targetEntity="Pim\Bundle\ProductBundle\Model\ProductValueInterface",
     *     mappedBy="entity",
     *     cascade={"persist", "remove"}
     * )
     */
    protected $values;

    /**
     * @var Pim\Bundle\ProductBundle\Entity\Family $family
     *
     * @ORM\ManyToOne(targetEntity="Pim\Bundle\ProductBundle\Entity\Family", cascade={"persist"})
     * @ORM\JoinColumn(name="family_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $family;

    /**
     * @var ArrayCollection $categories
     *
     * @ORM\ManyToMany(targetEntity="Pim\Bundle\ProductBundle\Model\CategoryInterface", mappedBy="products")
     */
    protected $categories;

    /**
     * @var boolean $enabled
     *
     * @ORM\Column(name="is_enabled", type="boolean")
     */
    protected $enabled = true;

    /**
     * @var ArrayCollection $completenesses
     *
     * @ORM\OneToMany(
     *     targetEntity="Pim\Bundle\ProductBundle\Entity\Completeness",
     *     mappedBy="product"
     * )
     */
    protected $completenesses;

    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        parent::__construct();

        $this->categories     = new ArrayCollection();
        $this->completenesses = new ArrayCollection();
    }

    /**
     * Get version
     *
     * @return string $version
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Get family
     *
     * @return \Pim\Bundle\ProductBundle\Entity\Family
     */
    public function getFamily()
    {
        return $this->family;
    }

    /**
     * Set family
     *
     * @param Family $family
     *
     * @return \Pim\Bundle\ProductBundle\Entity\Product
     */
    public function setFamily($family)
    {
        $this->family = $family;

        return $this;
    }

    /**
     * Get the identifier of the product
     *
     * @return ProductValue the identifier of the product
     *
     * @throws MissingIdentifierException if no identifier could be found
     */
    public function getIdentifier()
    {
        $values = array_filter(
            $this->getValues()->toArray(),
            function ($value) {
                return $value->getAttribute()->getAttributeType() === 'pim_product_identifier';
            }
        );

        if (false === $identifier = reset($values)) {
            throw new MissingIdentifierException($this);
        }

        return $identifier;
    }

    /**
     * Get the attributes of the product
     *
     * @return array the attributes of the current product
     */
    public function getAttributes()
    {
        return array_map(
            function ($value) {
                return $value->getAttribute();
            },
            $this->getValues()->toArray()
        );
    }

    /**
     * Get values
     *
     * @return ArrayCollection
     */
    public function getValues()
    {
        $_values = new ArrayCollection();

        foreach ($this->values as $value) {
            $attribute = $value->getAttribute();
            $key = $attribute->getCode();
            if ($attribute->getTranslatable()) {
                $key .= '_'.$value->getLocale();
            }
            if ($attribute->getScopable()) {
                $key .= '_'.$value->getScope();
            }
            $_values[$key] = $value;
        }

        return $_values;
    }

    /**
     * Get ordered group
     *
     * Group with negative sort order (Other) will be put at the end
     *
     * @return array
     */
    public function getOrderedGroups()
    {
        $groups = array_map(
            function ($attribute) {
                return $attribute->getVirtualGroup();
            },
            $this->getAttributes()
        );
        array_map(
            function ($group) {
                return (string) $group;
            },
            $groups
        );
        $groups = array_unique($groups);

        usort(
            $groups,
            function ($first, $second) {
                $first  = $first->getSortOrder();
                $second = $second->getSortOrder();

                if ($first === $second) {
                    return 0;
                }

                if ($first < $second && $first < 0) {
                    return 1;
                }

                if ($first > $second && $second < 0) {
                    return -1;
                }

                return $first < $second ? -1 : 1;
            }
        );

        return $groups;
    }

    /**
     * Get product label
     *
     * @param string $locale
     *
     * @return \Oro\Bundle\FlexibleEntityBundle\Model\mixed|string
     */
    public function getLabel($locale = null)
    {
        if ($this->family) {
            if ($attributeAsLabel = $this->family->getAttributeAsLabel()) {
                if ($locale) {
                    $this->setLocale($locale);
                }
                if ($value = $this->getValue($attributeAsLabel->getCode())) {
                    $data = $value->getData();
                    if (!empty($data)) {
                        return $data;
                    }
                }
            }
        }

        return $this->id;
    }

    /**
     * Get the product categories
     *
     * @return ArrayCollection
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * Set the product categories
     *
     * @param ArrayCollection $categories
     *
     * @return Procuct
     */
    public function setCategories($categories)
    {
        foreach ($categories as $category) {
            $category->addProduct($this);
        }
        $this->categories = $categories;

        return $this;
    }

    /**
     * Get a string with categories linked to product
     *
     * @return string
     */
    public function getCategoryCodes()
    {
        $codes = array();
        foreach ($this->getCategories() as $category) {
            $codes[] = $category->getCode();
        }

        return implode(',', $codes);
    }

    /**
     * Predicate to know if product is enabled or not
     *
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * Setter for predicate enabled
     *
     * @param bool $enabled
     *
     * @return \Pim\Bundle\ProductBundle\Entity\Product
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * Check if an attribute can be removed from the product
     *
     * @param ProductAttribute $attribute
     *
     * @return boolean
     */
    public function isAttributeRemovable(ProductAttribute $attribute)
    {
        if ('pim_product_identifier' === $attribute->getAttributeType()) {
            return false;
        }

        if (null === $this->getFamily()) {
            return true;
        }

        return !$this->getFamily()->getAttributes()->contains($attribute);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getLabel();
    }

    /**
     * Getter for product completenesses
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getCompletenesses()
    {
        return $this->completenesses;
    }

    /**
     * Add product completeness
     *
     * @param Completeness $completeness
     *
     * @return \Pim\Bundle\ProductBundle\Entity\Product
     */
    public function addCompleteness(Completeness $completeness)
    {
        if (!$this->completenesses->contains($completeness)) {
            $this->completenesses->add($completeness);
        }

        return $this;
    }

    /**
     * Remove product completeness
     *
     * @param Completeness $completeness
     *
     * @return \Pim\Bundle\ProductBundle\Entity\Product
     */
    public function removeCompleteness(Completeness $completeness)
    {
        $this->completenesses->removeElement($completeness);

        return $this;
    }

    /**
     * Get the product completeness from a locale and a scope
     *
     * @param string $locale
     * @param string $channel
     *
     * @return \Pim\Bundle\ProductBundle\Entity\Completeness|null
     */
    public function getCompleteness($locale, $channel)
    {
        $completeness = array_filter(
            $this->completenesses->toArray(),
            function ($completeness) use ($locale, $channel) {
                return $completeness->getLocale()->getCode() === $locale
                    && $completeness->getChannel()->getCode() === $channel;
            }
        );

        if (count($completeness) === 0) {
            return null;
        } else {
            return array_shift($completeness);
        }
    }

    /**
     * Set product completenesses
     *
     * @param array $completenesses
     *
     * @return \Pim\Bundle\ProductBundle\Entity\Product
     */
    public function setCompletenesses(array $completenesses = array())
    {
        $this->completenesses = new ArrayCollection($completenesses);

        return $this;
    }
}
