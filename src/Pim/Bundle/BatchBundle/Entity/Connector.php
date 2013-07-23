<?php

namespace Pim\Bundle\BatchBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Entity connector is an instance of a configured connector
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Table(name="pim_batch_connector")
 * @ORM\Entity()
 */
class Connector
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
     * Connector service id
     *
     * @var string
     *
     * @ORM\Column(name="service_id", type="string", length=255)
     */
    protected $serviceId;

    /**
     * Description
     *
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255, unique=true)
     */
    protected $description;

    /**
     * @var RawConfiguration $configuration
     *
     * @ORM\ManyToOne(targetEntity="RawConfiguration", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="raw_configuration_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $rawConfiguration;

    /**
     * @var Value
     *
     * @ORM\OneToMany(targetEntity="Job", mappedBy="connector", cascade={"persist", "remove"})
     */
    protected $jobs;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->jobs = new ArrayCollection();
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
     * Set id
     *
     * @param integer $id
     *
     * @return Connector
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Connector
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set connector service id
     *
     * @param string $serviceId
     *
     * @return Connector
     */
    public function setServiceId($serviceId)
    {
        $this->serviceId = $serviceId;

        return $this;
    }

    /**
     * Get connector service id
     *
     * @return string
     */
    public function getServiceId()
    {
        return $this->serviceId;
    }

    /**
     * Set connector configuration
     *
     * @param RawConfiguration $configuration
     *
     * @return Connector
     */
    public function setRawConfiguration(RawConfiguration $configuration)
    {
        $this->rawConfiguration = $configuration;

        return $this;
    }

    /**
     * Get connector configuration
     *
     * @return RawConfiguration
     */
    public function getRawConfiguration()
    {
        return $this->rawConfiguration;
    }

    /**
     * Add job
     *
     * @param Job $job
     *
     * @return Configuration
     */
    public function addJob(Job $job)
    {
        $this->jobs[] = $job;
        $job->setConnector($this);

        return $this;
    }

    /**
     * Remove job
     *
     * @param Job $job
     */
    public function removeJob(Job $job)
    {
        $this->jobs->removeElement($job);
    }

    /**
     * Get jobs
     *
     * @return \ArrayAccess
     */
    public function getJobs()
    {
        return $this->jobs;
    }
}
