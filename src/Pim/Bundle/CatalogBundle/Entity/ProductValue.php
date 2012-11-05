<?php
namespace Pim\Bundle\CatalogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;

/**
 * Value for a product field
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Table(name="PimCatalog_Product_Value")
 * @ORM\Entity
 * @Gedmo\TranslationEntity(class="Pim\Bundle\CatalogBundle\Entity\ProductTranslation")
 */
class ProductValue
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var Entity $product
     *
     * @ORM\ManyToOne(targetEntity="ProductEntity", inversedBy="values")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id")
     */
    protected $product;

    /**
    * @var Field $field
    *
    * @ORM\ManyToOne(targetEntity="ProductField")
    */
    protected $field;

    /**
     * TODO : basic sample for basic EAV implementation, only varchar values
     * @var string $content
     *
     * @Gedmo\Translatable
     * @ORM\Column(name="data", type="string", length=255)
     */
    private $data;

    /**
     * @Gedmo\Locale
     * Used locale to override Translation listener`s locale
     * this is not a mapped field of entity metadata, just a simple property
     */
    private $locale;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set data
     *
     * @param string $data
     * @return ProductValue
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Get data
     *
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set product
     *
     * @param Pim\Bundle\CatalogBundle\Entity\ProductEntity $product
     * @return ProductValue
     */
    public function setProduct(\Pim\Bundle\CatalogBundle\Entity\ProductEntity $product = null)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * Get product
     *
     * @return Pim\Bundle\CatalogBundle\Entity\ProductEntity
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * Set field
     *
     * @param Pim\Bundle\CatalogBundle\Entity\ProductField $field
     * @return ProductValue
     */
    public function setField(\Pim\Bundle\CatalogBundle\Entity\ProductField $field = null)
    {
        $this->field = $field;

        return $this;
    }

    /**
     * Get field
     *
     * @return Pim\Bundle\CatalogBundle\Entity\ProductField
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * Set used locale
     * @param string $locale
     */
    public function setTranslatableLocale($locale)
    {
        $this->locale = $locale;
    }
}