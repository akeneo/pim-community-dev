<?php
namespace Akeneo\CatalogBundle\Entity;

use Strixos\CoreBundle\Model\AbstractModel;
use Doctrine\ORM\Mapping as ORM;
/**
 * Lang entity
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * 
 * @ORM\Table(name="AkeneoCatalog_Lang")
 * @ORM\Entity
 */
class Lang extends AbstractModel
{
    /**
     * @staticvar string
     */
    const LANG_FR = 'fr_FR';
    const LANG_US = 'en_US';
    
   /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string $locale
     * 
     * @ORM\Column(name="locale", type="string", length=5, unique=true)
     */
    protected $locale;

    /**
     * @var string $isDefault
     *
     * @ORM\Column(name="is_default", type="boolean")
     */
    protected $isDefault;

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
     * Get locale
     * 
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }
    
    /**
     * Set locale
     * 
     * @param string $locale
     * @return Lang
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
        
        return $this;
    }
    
    /**
     * Get is default
     * 
     * @return boolean
     */
    public function getIsDefault()
    {
        return $this->isDefault;
    }
    
    /**
     * Set isDefault
     * 
     * @param boolean $isDefault
     * @return Lang
     */
    public function setIsDefault($isDefault)
    {
        $this->isDefault = $isDefault;
        
        return $this;
    }
}