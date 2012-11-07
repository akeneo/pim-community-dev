<?php
namespace Pim\Bundle\CatalogBundle\Document;

use Bap\Bundle\FlexibleEntityBundle\Model\EntityType as AbstractEntityType;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Doctrine\Common\Collections\ArrayCollection;
use APY\DataGridBundle\Grid\Mapping as GRID;

/**
 * Product type as Mongo Document
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @MongoDB\Document
 * @GRID\Source(columns="id, code")
 */
class ProductType extends AbstractEntityType
{
    /**
     * @MongoDB\Id
     * @GRID\Column()
     */
    protected $id;

    /**
     * @MongoDB\String
     * @GRID\Column()
     */
    protected $code;

    /**
     * @MongoDB\Raw
     * @var ArrayCollection
     */
    protected $titles = array();

    /**
     * @MongoDB\EmbedMany(targetDocument="ProductGroup")
     */
    protected $groups = array();

    /**
     * Used locale
     * @var string
     */
    protected $locale;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->groups = new ArrayCollection();
      // $this->fields = array(); //new ArrayCollection();
        $this->titles = array();

        // TODO: prepersist is not enought : MongoException: zero-length keys are not allowed, did you use $ with double quotes?

        $this->locale = 'en_US';
    }

    /**
    * Ensure there is a current locale used
    * @MongoDB\PostLoad¶
    */
    public function postLoad()
    {
        // TODO: use default application locale or current gui locale
        $this->locale = 'en_US';
    }

    /**
     * Ensure there is a current locale used
     * @MongoDB\PrePersist¶
     */
    public function prePersist()
    {
        // TODO: use default application locale or current gui locale
        if (!$this->locale) {
            $this->locale = 'en_US';
        }
    }

    /**
     * Set title
     *
     * @param string $title
     * @return ProductType
     */
    public function setTitle($title)
    {
        $this->titles[$this->locale] = $title;
        return $this;
    }

    /**
     * Get title
     *
     * @return string $title
     */
    public function getTitle()
    {
        return $this->titles[$this->locale];
    }

    /**
     * Get used locale
     * @return string $locale
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Set used locale
     * @param string $locale
     */
    public function setTranslatableLocale($locale)
    {
        $this->locale = $locale;
    }


    /**
     * Set titles
     *
     * @param raw $titles
     * @return ProductType
     */
    public function setTitles($titles)
    {
        $this->titles = $titles;
        return $this;
    }

    /**
     * Get titles
     *
     * @return raw $titles
     */
    public function getTitles()
    {
        return $this->titles;
    }
}
