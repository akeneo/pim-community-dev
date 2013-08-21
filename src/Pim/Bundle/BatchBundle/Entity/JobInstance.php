<?php

namespace Pim\Bundle\BatchBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Pim\Bundle\BatchBundle\Job\Job;

/**
 * Entity job
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Table(name="pim_job_instance")
 * @ORM\Entity()
 * @UniqueEntity(fields="code", message="This code is already taken")
 */
class JobInstance
{
    const STATUS_READY       = 0;
    const STATUS_DRAFT       = 1;
    const STATUS_IN_PROGRESS = 2;

    const TYPE_IMPORT = 'import';
    const TYPE_EXPORT = 'export';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=100, unique=true)
     * @Assert\NotBlank
     * @Assert\Regex(
     *     pattern="/^[a-zA-Z0-9_]+$/",
     *     message="The code must only contain alphanumeric characters and underscore."
     * )
     */
    protected $code;

    /**
     * @var string
     *
     * @ORM\Column(nullable=true)
     * @Assert\NotBlank
     */
    protected $label;

    /**
     * @var string
     *
     * @ORM\Column(name="alias", type="string", length=50)
     */
    protected $alias;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="integer")
     */
    protected $status = self::STATUS_READY;

    /**
     * @var string
     *
     * @ORM\Column(name="connector", type="string")
     */
    protected $connector;

    /**
     * JobInstance type export or import
     *
     * @var string
     *
     * @ORM\Column
     */
    protected $type;

    /**
     * @var array
     *
     * @ORM\Column(type="array")
     */
    protected $rawConfiguration = array();

    /**
     * @var Job
     * @Assert\Valid
     */
    protected $job;

    /**
     * @ORM\OneToMany(targetEntity="JobExecution", mappedBy="jobInstance")
     */
    protected $jobExecutions;

    /**
     * Constructor
     *
     * @param string $connector
     * @param string $type
     * @param string $alias
     */
    public function __construct($connector, $type, $alias)
    {
        $this->connector     = $connector;
        $this->type          = $type;
        $this->alias         = $alias;
        $this->jobExecutions = new ArrayCollection();
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
     *
     * @return \Pim\Bundle\BatchBundle\Entity\JobInstance
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
     * @return \Pim\Bundle\BatchBundle\Entity\JobInstance
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
     * Get connector
     *
     * @return string
     */
    public function getConnector()
    {
        return $this->connector;
    }

    /**
     * Get alias
     *
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
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
     * @return \Pim\Bundle\BatchBundle\Entity\JobInstance
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
     * @return \Pim\Bundle\BatchBundle\Entity\JobInstance
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
     * Set job configuration
     *
     * @param array $configuration
     *
     * @return \Pim\Bundle\BatchBundle\Entity\JobInstance
     */
    public function setRawConfiguration($configuration)
    {
        $this->rawConfiguration = $configuration;

        return $this;
    }

    /**
     * Get raw configuration
     *
     * @return array
     */
    public function getRawConfiguration()
    {
        return $this->rawConfiguration;
    }

    /**
     * Set job
     *
     * @param Job $job
     *
     * @return \Pim\Bundle\BatchBundle\Entity\JobInstance
     */
    public function setJob($job)
    {
        $this->job = $job;

        if ($job) {
            $this->setRawConfiguration($job->getConfiguration());
        }

        return $this;
    }

    /**
     * Get job
     *
     * @return Job
     */
    public function getJob()
    {
        return $this->job;
    }
}
