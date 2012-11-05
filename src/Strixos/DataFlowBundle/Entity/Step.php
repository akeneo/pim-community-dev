<?php
namespace Strixos\DataFlowBundle\Entity;

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
class Step
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
     * @ORM\ManyToOne(targetEntity="Job", inversedBy="steps")
     * @ORM\JoinColumn(name="job_id", referencedColumnName="id")
     */
    private $job;

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

    protected $messages; // TODO replace by a better mecanism

    /**
    * Constructor
    */
    public function __construct()
    {
        $this->messages = array();
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
     * TODO use composite and separated step and processers
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
        $this->options = json_encode($options);
        return $this;
    }

    /**
     * Get options
     *
     * @return string
     */
    public function getOptions()
    {
        return json_decode($this->options, true);
    }

    /**
     * Get option
     *
     * @param string $code
     * @return string
     */
    public function getOption($code)
    {
        $options = $this->getOptions();
        // TODO exception if code not exists
        return (isset($options[$code])) ? $options[$code] : null;
    }

    /**
     * Run this step
     * @param mixed $inputData
     * @return mixed $outputData
     */
    public function run($inputData = null)
    {
        // TODO abstract / runnable interface ?
    }


    /**
     * Set job
     *
     * @param Strixos\DataFlowBundle\Entity\Job $job
     * @return Step
     */
    public function setJob(\Strixos\DataFlowBundle\Entity\Job $job = null)
    {
        $this->job = $job;
        return $this;
    }

    /**
     * Get job
     *
     * @return Strixos\DataFlowBundle\Entity\Job
     */
    public function getJob()
    {
        return $this->job;
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

    /**
    * Add message
    *
    * @param string $msg
    * @return Step
    */
    public function addMessage($message)
    {
        $this->messages[] = $message;
        return $this;
    }
}