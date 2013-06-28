<?php
namespace Pim\Bundle\ProductBundle\Entity;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\ExecutionContext;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Handler\ArrayCollectionHandler;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;
use Oro\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityFlexible;
use Oro\Bundle\DataAuditBundle\Metadata\Annotation as Oro;
use Pim\Bundle\ConfigBundle\Entity\Locale;
use Pim\Bundle\ProductBundle\Model\ProductInterface;

/**
 * Flexible product
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Table(name="pim_product")
 * @ORM\Entity(repositoryClass="Pim\Bundle\ProductBundle\Entity\Repository\ProductRepository")
 * @Assert\Callback(methods={"isLocalesValid"})
 * @Oro\Loggable
 */
class Product extends AbstractEntityFlexible implements ProductInterface
{
    /**
     * @var Value
     *
     * @ORM\OneToMany(
     *     targetEntity="Pim\Bundle\ProductBundle\Model\ProductValueInterface",
     *     mappedBy="entity",
     *     cascade={"persist", "remove"}
     * )
     * @Oro\Versioned
     */
    protected $values;

    /**
     * @var productFamily
     *
     * @ORM\ManyToOne(targetEntity="Pim\Bundle\ProductBundle\Entity\ProductFamily", cascade={"persist"})
     * @ORM\JoinColumn(name="family_id", referencedColumnName="id", onDelete="SET NULL")
     * @Oro\Versioned("getCode")
     */
    protected $productFamily;

    /**
     * @var ArrayCollection $locales
     *
     * @ORM\ManyToMany(targetEntity="Pim\Bundle\ConfigBundle\Entity\Locale", cascade={"persist"})
     * @ORM\JoinTable(
     *    name="pim_product_locale",
     *    joinColumns={@ORM\JoinColumn(name="product_id", referencedColumnName="id", onDelete="CASCADE")},
     *    inverseJoinColumns={@ORM\JoinColumn(name="locale_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     * @Oro\Versioned("getCode")
     */
    protected $locales;

    /**
     * @var ArrayCollection $categories
     *
     * @ORM\ManyToMany(targetEntity="Pim\Bundle\ProductBundle\Model\CategoryInterface", mappedBy="products")
     */
    protected $categories;

    /**
     * @ORM\Column(name="is_enabled", type="boolean")
     *  @Oro\Versioned
     */
    protected $enabled = true;

    /**
     * Redefine constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->locales = new ArrayCollection();
    }

    /**
     * Get product family
     *
     * @return \Pim\Bundle\ProductBundle\Entity\ProductFamily
     */
    public function getProductFamily()
    {
        return $this->productFamily;
    }

    /**
     * Set product family
     *
     * @param ProductFamily $productFamily
     *
     * @return \Pim\Bundle\ProductBundle\Entity\Product
     */
    public function setProductFamily($productFamily)
    {
        $this->productFamily = $productFamily;

        return $this;
    }

    /**
     * Get locales
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getLocales()
    {
        return $this->locales;
    }

    /**
     * Add locale
     *
     * @param \Pim\Bundle\ConfigBundle\Entity\Locale $locale
     *
     * @return \Pim\Bundle\ProductBundle\Entity\Product
     */
    public function addLocale(\Pim\Bundle\ConfigBundle\Entity\Locale $locale)
    {
        $this->locales[] = $locale;

        return $this;
    }

    /**
     * Remove locale
     *
     * @param \Pim\Bundle\ConfigBundle\Entity\Locale $locale
     *
     * @return \Pim\Bundle\ProductBundle\Entity\Product
     */
    public function removeLocale(\Pim\Bundle\ConfigBundle\Entity\Locale $locale)
    {
        $this->locales->removeElement($locale);
    }

    /**
     * Check if product is activated on that locale
     *
     * @param string $localeCode
     *
     * @return boolean
     */
    public function isEnabledForLocale($localeCode)
    {
        $locales = $this->locales->filter(
            function ($locale) use ($localeCode) {
                return ($locale->getCode() === $localeCode);
            }
        );

        return $locales->count() > 0;
    }

    /**
     * Set locales
     *
     * @param ArrayCollection $locales
     *
     * @return \Pim\Bundle\ProductBundle\Entity\Product
     */
    public function setLocales($locales)
    {
        $this->locales = $locales;

        return $this;
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
            function ($value) {
                return $value->getVirtualGroup();
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
            function ($a, $b) {
                $a = $a->getSortOrder();
                $b = $b->getSortOrder();

                if ($a === $b) {
                    return 0;
                }

                if ($a < $b && $a < 0) {
                    return 1;
                }

                if ($a > $b && $b < 0) {
                    return -1;
                }

                return $a < $b ? -1 : 1;
            }
        );

        return $groups;
    }

    /**
     * Make sure that at least one locale has been added to the product
     *
     * @param ExecutionContext $context Execution Context
     */
    public function isLocalesValid(ExecutionContext $context)
    {
        if ($this->locales->count() == 0) {
            $context->addViolationAtPath(
                $context->getPropertyPath() . '.locales',
                'Please specify at least one activated locale'
            );
        }
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
        if ($this->productFamily) {
            if ($attributeAsLabel = $this->productFamily->getAttributeAsLabel()) {
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
     * Get a string with categories linked to product
     *
     * @return string
     */
    public function getCategoryTitlesAsString()
    {
        $titles = array();
        foreach ($this->getCategories() as $category) {
            $titles[] = $category->getTitle();
        }

        return implode(', ', $titles);
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
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getLabel();
    }
}
