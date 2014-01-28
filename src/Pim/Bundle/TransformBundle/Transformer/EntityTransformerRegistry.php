<?php

namespace Pim\Bundle\TransformBundle\Transformer;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Entity transformer registry
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EntityTransformerRegistry implements EntityTransformerInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var string
     */
    protected $defaultTransformerId;

    /**
     * @var array
     */
    protected $transformerIds = array();

    /**
     * Constructor
     *
     * @param ContainerInterface $container
     * @param string             $defaultTransformerId
     */
    public function __construct(ContainerInterface $container, $defaultTransformerId)
    {
        $this->container = $container;
        $this->defaultTransformerId = $defaultTransformerId;
    }

    /**
     * Adds an entity transformer to the registry
     *
     * @param string $class
     * @param string $serviceId
     */
    public function addEntityTransformer($class, $serviceId)
    {
        $this->transformerIds[$class] = $serviceId;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($class, array $data, array $defaults = array())
    {
        return $this->getEntityTransformer($class)->transform($class, $data, $defaults);
    }

    /**
     * {@inheritdoc}
     */
    public function getErrors($class)
    {
        return $this->getEntityTransformer($class)->getErrors($class);
    }

    /**
     * {@inheritdoc}
     */
    public function getTransformedColumnsInfo($class)
    {
        return $this->getEntityTransformer($class)->getTransformedColumnsInfo($class);
    }

    /**
     * @param string $class
     *
     * @return EntityTransformerInterface
     */
    protected function getEntityTransformer($class)
    {
        $serviceId = isset($this->transformerIds[$class]) ? $this->transformerIds[$class] : $this->defaultTransformerId;

        return $this->container->get($serviceId);
    }
}
