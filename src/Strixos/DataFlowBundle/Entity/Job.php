<?php
namespace Strixos\DataFlowBundle\Entity;

use Strixos\CoreBundle\Model\AbstractModel;
use Doctrine\ORM\Mapping as ORM;

/**
 *
 * @author    Nicolas Dupont @ Strixos
 * @copyright Copyright (c) 2012 Strixos SAS (http://www.strixos.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * Strixos\DataFow\Entity\Job
 *
 * TODO: job with sub-job ? (composite pattern)
 *
 * @ORM\Table(name="StrixosDataFlow_Job")
 * @ORM\Entity
 */
class Job extends AbstractModel
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
    * @ORM\ManyToMany(targetEntity="Step")
    * @ORM\JoinTable(name="StrixosDataFlow_Job_Step",
    *      joinColumns={@ORM\JoinColumn(name="step_id", referencedColumnName="id")},
    *      inverseJoinColumns={@ORM\JoinColumn(name="job_id", referencedColumnName="id")}
    *      )
    */
    protected $orderedSteps;

    /**
     * Run ordered steps
     */
    public function run()
    {
        // TODO
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->orderedSteps = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Job
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
     * Add orderedSteps
     *
     * @param Strixos\DataFlowBundle\Entity\Step $orderedSteps
     * @return Job
     */
    public function addOrderedStep(\Strixos\DataFlowBundle\Entity\Step $orderedSteps)
    {
        $this->orderedSteps[] = $orderedSteps;
    
        return $this;
    }

    /**
     * Remove orderedSteps
     *
     * @param Strixos\DataFlowBundle\Entity\Step $orderedSteps
     */
    public function removeOrderedStep(\Strixos\DataFlowBundle\Entity\Step $orderedSteps)
    {
        $this->orderedSteps->removeElement($orderedSteps);
    }

    /**
     * Get orderedSteps
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getOrderedSteps()
    {
        return $this->orderedSteps;
    }
}