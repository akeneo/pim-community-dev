<?php
namespace Pim\Bundle\ProductBundle\Entity;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\ExecutionContext;
use Symfony\Component\Validator\Constraints as Assert;
use Oro\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityFlexible;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\ConfigBundle\Entity\Locale;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Flexible product
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Table(name="pim_product")
 * @ORM\Entity(repositoryClass="Pim\Bundle\ProductBundle\Entity\Repository\ProductRepository")
 * @UniqueEntity("sku");
 * @Assert\Callback(methods={"isLocalesValid"})
 * @Gedmo\Loggable(logEntryClass="Oro\Bundle\DataAuditBundle\Entity\Audit")
 */
class Product extends AbstractEntityFlexible
{
    /**
     * @var string $sku
     *
     * @ORM\Column(name="sku", type="string", length=255, unique=true)
     * @Assert\NotNull()
     * @Gedmo\Versioned
     */
    protected $sku;

    /**
     * @var Value
     *
     * @ORM\OneToMany(targetEntity="ProductValue", mappedBy="entity", cascade={"persist", "remove"})
     */
    protected $values;

    /**
     * @var productFamily
     *
     * @ORM\ManyToOne(targetEntity="ProductFamily")
     * @ORM\JoinColumn(name="family_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $productFamily;

    /**
     * @var ArrayCollection $locales
     *
     * @ORM\ManyToMany(targetEntity="Pim\Bundle\ConfigBundle\Entity\Locale")
     * @ORM\JoinTable(
     *    name="pim_product_locale",
     *    joinColumns={@ORM\JoinColumn(name="product_id", referencedColumnName="id", onDelete="CASCADE")},
     *    inverseJoinColumns={@ORM\JoinColumn(name="locale_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     */
    protected $locales;

    /**
     * @var ArrayCollection $categories
     *
     * @ORM\ManyToMany(targetEntity="Category", mappedBy="products")
     */
    protected $categories;

    /**
     * Redefine constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->locales = new ArrayCollection();
    }

    /**
     * Get sku
     *
     * @return string
     */
    public function getSku()
    {
        return $this->sku;
    }

    /**
     * Set sku
     *
     * @param string $sku
     *
     * @return \Pim\Bundle\ProductBundle\Entity\Product
     */
    public function setSku($sku)
    {
        $this->sku = $sku;

        return $this;
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
     * Get ordered group
     *
     * Group with negative sort order (Other) will be put at the end
     *
     * @return string|number|multitype:
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
     * @return \Oro\Bundle\FlexibleEntityBundle\Model\mixed|string
     */
    public function getLabel()
    {
        if ($this->productFamily) {
            if ($attributeAsLabel = $this->productFamily->getAttributeAsLabel()) {
                $value = $this->getValue($attributeAsLabel->getCode());
                if ($value) {
                    $data = $value->getData();
                    if (!empty($data)) {
                        return $data;
                    }
                }
            }
        }

        return $this->sku;
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
}
