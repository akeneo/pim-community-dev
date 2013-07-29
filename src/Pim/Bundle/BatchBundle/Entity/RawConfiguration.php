<?php

namespace Pim\Bundle\BatchBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Pim\Bundle\BatchBundle\Configuration\ConfigurationInterface;
use Pim\Bundle\BatchBundle\Configuration\ConfigurationException;

/**
 * Entity configuration
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 *
 * @ORM\Table(name="pim_batch_raw_configuration")
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks()
 */
class RawConfiguration
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
     * @var string
     *
     * @ORM\Column(name="type_name", type="string", length=255)
     */
    protected $typeName;

    /**
     * @var string
     *
     * @ORM\Column(name="format", type="string", length=20)
     */
    protected $format;

    /**
     * @var string
     *
     * @ORM\Column(name="data", type="text")
     */
    protected $data;

    /**
     * @var ConfigurationInterface
     */
    protected $configuration;

    /**
     * Constructor
     *
     * @param ConfigurationInterface $configuration
     */
    public function __construct($configuration = null)
    {
        $this->configuration = $configuration;
        $this->format        = 'json';
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
     * Set configuration
     *
     * @param ConfigurationInterface $configuration
     *
     * @return RawConfiguration
     */
    public function setConfiguration(ConfigurationInterface $configuration)
    {
        $this->configuration = $configuration;

        return $this;
    }

    /**
     * Get configuration
     *
     * @return ConfigurationInterface
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * Serialize on pre flush (persist and update)
     * @ORM\PreFlush
     */
    public function preFlush()
    {
        $this->serialize();
    }

    /**
     * Deserialize on after load event listener
     * @ORM\PostLoad
     */
    public function postLoad()
    {
        $this->deserialize();
    }

    /**
     * Serialize data
     *
     * @return RawConfiguration
     */
    protected function serialize()
    {
        if (is_null($this->configuration)) {
            throw new ConfigurationException('Concrete configuration must be defined before serialize');
        }
        $serializer = \JMS\Serializer\SerializerBuilder::create()->build();
        $this->data      = $serializer->serialize($this->configuration, $this->format);
        $this->typeName  = get_class($this->configuration);

        return $this;
    }

    /**
     * Deserialize data
     *
     * @return RawConfiguration
     */
    protected function deserialize()
    {
        if (is_null($this->typeName)) {
            throw new ConfigurationException('type name must be defined before deserialize');
        }
        $serializer = \JMS\Serializer\SerializerBuilder::create()->build();
        $this->configuration = $serializer->deserialize($this->data, $this->typeName, $this->format);

        return $this;
    }
}
