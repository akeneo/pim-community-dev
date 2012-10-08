<?php
namespace Strixos\IcecatConnectorBundle\Entity;

use Strixos\CoreBundle\Model\AbstractModel;
use Doctrine\ORM\Mapping as ORM;

/**
 * @author     Nicolas Dupont @ Strixos
 * @copyright  Copyright (c) 2012 Strixos SAS (http://www.strixos.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Table(name="StrixosIcecatConnector_Supplier")
 * @ORM\Entity
 */
class Supplier extends AbstractModel
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
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string $icecatId
     *
     * @ORM\Column(name="icecat_id", type="integer", unique=true)
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
     * Set name
     *
     * @param string $name
     * @return Supplier
     */
    public function setName($name)
    {
        $this->name = $name;
    
        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set icecatId
     *
     * @param integer $icecatId
     * @return Supplier
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
}