<?php
namespace Strixos\DataFlowBundle\Entity;

use Strixos\CoreBundle\Model\AbstractModel;
use Doctrine\ORM\Mapping as ORM;

/**
 * @author     Nicolas Dupont @ Strixos
 * @copyright  Copyright (c) 2012 Strixos SAS (http://www.strixos.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * Strixos\DataFow\Entity\Step
 *
 * TODO: define options
 *
 * @ORM\Table(name="StrixosDataFlow_Step")
 * @ORM\Entity
 */
class Step extends AbstractModel
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
    * @var string $behaviour
    *
    * @ORM\Column(name="behaviour", type="string")
    */
    private $behaviour;


    /**
    * @var string $options
    *
    * @ORM\Column(name="options", type="string")
    */
    private $options;

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
     * @return Step
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
     * Set behaviour
     *
     * @param string $behaviour
     * @return Step
     */
    public function setBehaviour($behaviour)
    {
        $this->behaviour = $behaviour;

        return $this;
    }

    /**
     * Get behaviour
     *
     * @return string
     */
    public function getBehaviour()
    {
        return $this->behaviour;
    }

    /**
     * Set options
     *
     * @param string $options
     * @return Step
     */
    public function setOptions($options)
    {
        $this->options = $options;
    
        return $this;
    }

    /**
     * Get options
     *
     * @return string 
     */
    public function getOptions()
    {
        return $this->options;
    }
}