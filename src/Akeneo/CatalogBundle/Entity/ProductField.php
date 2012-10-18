<?php
namespace Akeneo\CatalogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Product field as sku, name, etc
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Table(name="AkeneoCatalog_Product_Field")
 * @ORM\Entity
 */
class ProductField
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string $code
     *
     * @ORM\Column(name="code", type="string", length=255, unique=true)
     */
    private $code;

    /**
     * @var string $title
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
    * @var string $type
    *
    * @ORM\Column(name="type", type="string", length=255)
    */
    private $type;



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
     * Set code
     *
     * @param string $code
     * @return ProductField
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return ProductField
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return ProductField
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * create code for product field
     *
     * @static
     * @param string $prefix
     * @param integer $vendorId
     * @param integer $categoryId
     * @return string
     *
     * TODO : Use method to slugify field name
     * TODO : If field name changes, all codes must be corrected to verify unicity
     */
    public static function createCode($prefix, $vendorId, $categoryId)
    {
//     	echo 'create ProductField code : '. strtolower($prefix.'-'.$vendorId.'-'.$categoryId) .'<br />';
        return strtolower($prefix.'-'.$vendorId.'-'.$categoryId);
    }
}