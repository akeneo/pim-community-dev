<?php
namespace Pim\Bundle\CatalogBundle\Document;

use Bap\Bundle\FlexibleEntityBundle\Model\Entity as AbstractEntity;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;
use APY\DataGridBundle\Grid\Mapping as GRID;

/**
 * Product as Mongo Document
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @MongoDB\Document
 * @GRID\Source(columns="id")
 */
class ProductEntity extends AbstractEntity
{
    //@see https://doctrine-mongodb-odm.readthedocs.org/en/latest/reference/annotations-reference.html

    /**
     * @MongoDB\Id
     * @GRID\Column()
     */
    protected $id;

    /**
     * Simple reference, only store id TODO: test
     * @MongoDB\ReferenceOne(targetDocument="ProductType", simple=true)
     */
    protected $type;

    /**
     * @MongoDB\ReferenceMany(targetDocument="ProductValue", cascade={"persist"})
     * @var ArrayCollection
     */
    protected $values = array();

    /**
    * TODO: problem : how to deal with typing ? define custom type to enforce this check ?
    * TODO: no use values but directly set variable ... problem with load
    *
    * @MongoDB\Raw
    * @var ArrayCollection
    */
    protected $values_en_US = array();

    /**
    * TODO: problem : how to deal with typing ? define custom type to enforce this check ?
    * TODO: no use values but directly set variable ... problem with load
    *
    * @MongoDB\Raw
    * @var ArrayCollection
    */
    protected $values_fr_FR = array();

    // TODO deal with dynamic locale add, when testing with sub array as values.en_US[field] there is problem with doctrine query builder on field

    /**
    * Used locale
    * @var string
    */
    protected $locale;

    // TODO refactor in superclass mapped

    /**
    * Constructor
    */
    public function __construct()
    {
        // TODO: prepersist is not enought : MongoException: zero-length keys are not allowed, did you use $ with double quotes?

        $this->locale = 'en_US';

        $this->values = new \Doctrine\Common\Collections\ArrayCollection();
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
}
