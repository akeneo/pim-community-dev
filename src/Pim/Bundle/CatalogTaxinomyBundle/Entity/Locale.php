<?php
namespace Pim\Bundle\CatalogTaxinomyBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Catalog locale
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Table(name="akeneo_catalogtaxinomy_locale")
 * @ORM\Entity
 */
class Locale
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
     * The ISO639-1 language code, an underscore (_), then the ISO3166 Alpha-2 country code (e.g. fr_FR for
     * French/France) is recommended.
     * @var string $code
     * @ORM\Column(name="code", type="string", length=5, unique=true)
     */
    protected $code;

    /**
     * @ORM\Column(name="is_default", type="boolean")
     */
    protected $isDefault;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->isDefault = false;
    }

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
     *
     * @return ChannelLocale
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
     * Set as default channel
     *
     * @param boolean $default
     *
     * @return Channel
     */
    public function setIsDefault($default)
    {
        $this->isDefault = $default;

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

}
