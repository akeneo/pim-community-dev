<?php

namespace Pim\Bundle\ProductBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Pim\Bundle\ConfigBundle\Entity\Locale;

/**
 * Product locale
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Entity
 * @ORM\Table(name="pim_product_locale")
 */
class ProductLocale
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="locales")
     */
    protected $product;

    /**
     * @ORM\ManyToOne(targetEntity="Pim\Bundle\ConfigBundle\Entity\Locale")
     */
    protected $locale;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $active = false;

    /**
     * @param Product $product
     */
    public function setProduct(Product $product)
    {
        $this->product = $product;
    }

    /**
     * @return Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @param Locale $locale
     */
    public function setLocale(Locale $locale)
    {
        $this->locale = $locale;
    }

    /**
     * @return Locale
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param boolean $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * @return boolean
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->locale->getCode();
    }

    /**
     * @param string $locale
     *
     * @return string
     */
    public function fromLocale($locale)
    {
        return $this->locale->fromLocale($locale);
    }
}
