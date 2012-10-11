<?php
namespace Akeneo\CatalogBundle\Document;

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
class ProductTypeMongo
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
     * TODO: groups to organize fields but with not strong constraint to keep light model relation
     * @MongoDB\Raw
     * @var ArrayCollection
     */
    protected $groups = array();

    /**
     * @MongoDB\ReferenceMany(targetDocument="ProductFieldMongo", cascade={"persist"})
     * @var ArrayCollection
     */
    protected $fields = array();

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
        $this->groups = array(); // TODO problem when using array collection to store hashmap
        $this->fields = array(); //new ArrayCollection();
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
     * Get id
     *
     * @return id $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set code
     *
     * @param string $code
     * @return ProductTypeMongo
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * Get code
     *
     * @return string $code
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Add fields
     *
     * @param Akeneo\CatalogBundle\Document\ProductFieldMongo $fields
     * @param string $groupCode
     */
    public function addFieldToGroup(\Akeneo\CatalogBundle\Document\ProductFieldMongo $field, $groupCode)
    {
        $this->fields[] = $field;
        if (!isset($this->groups[$groupCode])) {
            $this->groups[$groupCode] = array();
        }
        $this->groups[$groupCode][] = $field->getCode();
    }

    /**
     * Get fields
     *
     * @return Doctrine\Common\Collections\Collection $fields
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Add fields
     *
     * @param string $groupCode
     */
    public function addGroup($groupCode)
    {
        if (!isset($this->groups[$groupCode])) {
            $this->groups[$groupCode] = array();
        }
    }

    /**
     * Set groups
     *
     * @param raw $groups
     * @return ProductTypeMongo
     */
    public function setGroups($groups)
    {
        $this->groups = $groups;
        return $this;
    }

    /**
     * Get groups
     *
     * @return raw $groups
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * Add fields
     *
     * @param Akeneo\CatalogBundle\Document\ProductFieldMongo $fields
     */
    public function addFields(\Akeneo\CatalogBundle\Document\ProductFieldMongo $fields)
    {
        $this->fields[] = $fields;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return ProductTypeMongo
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
     * @return ProductTypeMongo
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
