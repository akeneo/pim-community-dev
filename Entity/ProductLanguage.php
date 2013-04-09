<?php

namespace Pim\Bundle\ProductBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Pim\Bundle\ConfigBundle\Entity\Language;

/**
 * @author    Gildas Quéméner <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Entity
 * @ORM\Table(name="pim_product_language")
 */
class ProductLanguage
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="languages")
     */
    protected $product;

    /**
     * @ORM\ManyToOne(targetEntity="Pim\Bundle\ConfigBundle\Entity\Language")
     */
    protected $language;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $active = false;

    public function setProduct(Product $product)
    {
        $this->product = $product;
    }

    public function getProduct()
    {
        return $this->product;
    }

    public function setLanguage(Language $language)
    {
        $this->language = $language;
    }

    public function getLanguage()
    {
        return $this->language;
    }

    public function setActive($active)
    {
        $this->active = $active;
    }

    public function isActive()
    {
        return $this->active;
    }

    public function getCode()
    {
        return $this->language->getCode();
    }

    public function fromLocale($locale)
    {
        return $this->language->fromLocale($locale);
    }
}

