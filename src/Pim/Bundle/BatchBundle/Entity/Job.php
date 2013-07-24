<?php

namespace Pim\Bundle\BatchBundle\Entity;

use Pim\Bundle\BatchBundle\Job\SimpleJob;

use Doctrine\ORM\Mapping as ORM;

/**
 * Entity job is an instance of a configured job for a configured connector
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Table(name="pim_job")
 * @ORM\Entity()
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
    protected $id;

    /**
     * @var string $code
     *
     * @ORM\Column(name="code", type="string", length=100)
     */
    protected $code;

    /**
     * @var string $label
     *
     * @ORM\Column(name="label", type="string", length=255)
     */
    protected $label;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="integer")
     */
    protected $status;

    /**
     * @var Connector $connector
     *
     * @ORM\ManyToOne(targetEntity="Connector", inversedBy="jobs")
     * @ORM\JoinColumn(name="connector_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $connector;

    /**
     * Job type export or import
     *
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255)
     */
    protected $type = 'export'; // TODO: temporary, must be setuped during creation

    /**
     * @var RawConfiguration $connectorConfiguration
     *
     * @ORM\ManyToOne(targetEntity="RawConfiguration", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="raw_configuration_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $rawConfiguration;

    /**
     * @var SimpleJob
     */
    protected $jobDefinition;

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
     * @return \Pim\Bundle\BatchBundle\Entity\Job
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
     * Set label
     *
     * @param string $label
     *
     * @return \Pim\Bundle\BatchBundle\Entity\Job
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Get status
     *
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set status
     *
     * @param integer $status
     *
     * @return \Pim\Bundle\BatchBundle\Entity\Job
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return \Pim\Bundle\BatchBundle\Entity\Job
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set connector
     *
     * @param Connector $connector
     *
     * @return \Pim\Bundle\BatchBundle\Entity\Job
     */
    public function setConnector(Connector $connector)
    {
        $this->connector = $connector;

        return $this;
    }

    /**
     * Get connector configuration
     *
     * @return Connector
     */
    public function getConnector()
    {
        return $this->connector;
    }

    /**
     * Set job service id
     *
     * @param string $serviceId
     *
     * @return \Pim\Bundle\BatchBundle\Entity\Job
     */
    public function setServiceId($serviceId)
    {
        $this->serviceId = $serviceId;

        return $this;
    }

    /**
     * Get job service id
     *
     * @return string
     */
    public function getServiceId()
    {
        return $this->serviceId;
    }

    /**
     * Set job configuration
     *
     * @param RawConfiguration $configuration
     *
     * @return \Pim\Bundle\BatchBundle\Entity\Job
     */
    public function setRawConfiguration(RawConfiguration $configuration)
    {
        $this->rawConfiguration = $configuration;

        return $this;
    }

    /**
     * Get job configuration
     *
     * @return RawConfiguration
     */
    public function getRawConfiguration()
    {
        return $this->rawConfiguration;
    }

    /**
     * Set job definition
     *
     * @param string $jobDefinition
     *
     * @return \Pim\Bundle\BatchBundle\Entity\Job
     */
    public function setJobDefinition($jobDefinition)
    {
        $this->jobDefinition = $jobDefinition;

        return $this;
    }

    /**
     * Get job definition
     *
     * @return string
     */
    public function getJobDefinition()
    {
        return $this->jobDefinition;
    }
}
