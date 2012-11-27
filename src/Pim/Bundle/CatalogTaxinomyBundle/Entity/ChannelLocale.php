<?php
namespace Pim\Bundle\CatalogTaxinomyBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Catalog channel locale, aims to define scopes
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Table(name="Akeneo_PimCatalogTaxinomy_ChannelLocale")
 * @ORM\Entity
 */
class ChannelLocale
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
     * @var string $localeCode
     * @ORM\Column(name="code", type="string", length=5)
     */
    protected $code;

    /**
     * @var Entity $channel
     *
     * @ORM\ManyToOne(targetEntity="Channel", inversedBy="locales")
     * @ORM\JoinColumn(name="channel_id", referencedColumnName="id")
     */
    protected $channel;

    /**
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

    /**
     * Set channel
     *
     * @param Pim\Bundle\CatalogTaxinomyBundle\Entity\Channel $channel
     * 
     * @return ChannelLocale
     */
    public function setChannel(\Pim\Bundle\CatalogTaxinomyBundle\Entity\Channel $channel = null)
    {
        $this->channel = $channel;

        return $this;
    }

    /**
     * Get channel
     *
     * @return Pim\Bundle\CatalogTaxinomyBundle\Entity\Channel
     */
    public function getChannel()
    {
        return $this->channel;
    }
}
