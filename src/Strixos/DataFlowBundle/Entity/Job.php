<?php
namespace Strixos\DataFlowBundle\Entity;

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
class Job
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
    * @ORM\OneToMany(targetEntity="Step", mappedBy="job")
    */
    protected $steps;

    protected $entityManager;

    protected $documentManager;

    protected $messages;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->steps = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Run ordered steps
     * @param mixed $inputData
     * @return mixed $outputData
     */
    public function run($inputData = null)
    {
        $this->messages = array();
        foreach ($this->getSteps() as $step) {
            // behaviour contains the implemented step class
            $class = $step->getBehaviour();
            $stepImpl = new $class();
            $stepImpl->setJob($this);
            $stepImpl->setOptions($step->getOptions());
            // run the step with precedent step input data
            $inputData = $stepImpl->run($inputData);
            // add step message to stack
            $this->messages = array_merge($this->messages, $stepImpl->getMessages());
        }
        return $inputData;
    }

    /**
    * Set manager
    *
    * @param string $entitymanager
    * @return Job
    */
    public function setEntityManager($entitymanager)
    {
        $this->entityManager = $entitymanager;
        return $this;
    }

    /**
     * Get manager
     *
     * @return string
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
    * Set manager
    *
    * @param string $documentmanager
    * @return Job
    */
    public function setDocumentManager($documentmanager)
    {
        $this->documentManager = $documentmanager;
        return $this;
    }

    /**
     * Get manager
     *
     * @return string
     */
    public function getDocumentManager()
    {
        return $this->documentManager;
    }

    /**
     * Add steps
     *
     * @param Strixos\DataFlowBundle\Entity\Step $steps
     * @return Job
     */
    public function addStep(\Strixos\DataFlowBundle\Entity\Step $steps)
    {
        $this->steps[] = $steps;

        return $this;
    }

    /**
     * Remove steps
     *
     * @param Strixos\DataFlowBundle\Entity\Step $steps
     */
    public function removeStep(\Strixos\DataFlowBundle\Entity\Step $steps)
    {
        $this->steps->removeElement($steps);
    }

    /**
     * Get steps
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getSteps()
    {
        return $this->steps;
    }

    /**
     * Get messages
     *
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }
}