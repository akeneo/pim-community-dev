<?php
namespace Pim\Bundle\ConnectorIcecatBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Icecat supplier
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Table(name="akeneo_connectoricecat_sourcesupplier")
 * @ORM\Entity
 */
class SourceSupplier
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
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    protected $name;

    /**
     * @var string $icecatId
     *
     * @ORM\Column(name="icecat_id", type="integer", unique=true)
     */
    protected $icecatId;

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
     *
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
     *
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
