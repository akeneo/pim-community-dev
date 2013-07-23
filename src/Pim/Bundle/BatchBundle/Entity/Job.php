<?php

namespace Pim\Bundle\BatchBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Entity job is an instance of a configured job for a configured connector
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Table(name="pim_batch_job")
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
     * Description
     *
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255)
     */
    protected $description;

    /**
     * @var Connector $connector
     *
     * @ORM\ManyToOne(targetEntity="Connector", inversedBy="jobs")
     * @ORM\JoinColumn(name="connector_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $connector;

    /**
     * Job service id
     *
     * @var string
     *
     * @ORM\Column(name="service_id", type="string", length=255)
     */
    protected $serviceId;

    /**
     * @var RawConfiguration $connectorConfiguration
     *
     * @ORM\ManyToOne(targetEntity="RawConfiguration", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="raw_configuration_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $rawConfiguration;

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
     * @return Job
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
     * Set connector
     *
     * @param Connector $connector
     *
     * @return Job
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
     * @return Job
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
     * @return Job
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
}
