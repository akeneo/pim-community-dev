<?php
namespace Strixos\IcecatConnectorBundle\Entity;

use Strixos\CoreBundle\Model\AbstractModel;
use Doctrine\ORM\Mapping as ORM;

/**
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Table(name="StrixosIcecatConnector_SourceLanguage")
 * @ORM\Entity
 */
class SourceLanguage extends AbstractModel
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
     * @ORM\Column(name="code", type="string", length=50, unique=true)
     */
    private $code;

    /**
     * @var string $shortCode
     *
     * @ORM\Column(name="shortCode", type="string", length=5, unique=true)
     */
    private $shortCode;

    /**
     * @var string $icecatShortCode
     *
     * @ORM\Column(name="icecatShortCode", type="string", length=5, unique=true)
     */
    private $icecatShortCode;

    /**
     * @var integer $icecatId
     *
     * @ORM\Column(name="icecat_id", type="integer")
     */
    private $icecatId;

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
     * @return Language
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
     * Set shortCode
     *
     * @param string $shortCode
     * @return Language
     */
    public function setShortCode($shortCode)
    {
        $this->shortCode = $shortCode;

        return $this;
    }

    /**
     * Get shortCode
     *
     * @return string
     */
    public function getShortCode()
    {
        return $this->shortCode;
    }

    /**
     * Set icecatId
     *
     * @param integer $icecatId
     * @return Language
     */
    public function setIcecatId($icecatId)
    {
        $this->icecatId = $icecatId;

        return $this;
    }

    /**
     * Get icecatId
     *
     * @return integer
     */
    public function getIcecatId()
    {
        return $this->icecatId;
    }

    /**
     * Set icecatShortCode
     *
     * @param string $icecatShortCode
     * @return Language
     */
    public function setIcecatShortCode($icecatShortCode)
    {
        $this->icecatShortCode = $icecatShortCode;

        return $this;
    }

    /**
     * Get icecatShortCode
     *
     * @return string
     */
    public function getIcecatShortCode()
    {
        return $this->icecatShortCode;
    }
}