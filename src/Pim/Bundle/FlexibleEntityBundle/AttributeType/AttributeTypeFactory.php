<?php

namespace Pim\Bundle\FlexibleEntityBundle\AttributeType;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The attribute type factory
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeTypeFactory
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var array
     */
    protected $types;

    /**
     * @param ContainerInterface $container
     * @param array              $types
     */
    public function __construct(ContainerInterface $container, array $types = array())
    {
        $this->container = $container;
        $this->types     = $types;
    }

    /**
     * Get the attribute type service
     *
     * @param string $type
     *
     * @return AttributeTypeInterface
     * @throws \RunTimeException
     */
    public function get($type)
    {
        if (!$type) {
            throw new \RunTimeException('The type must be defined');
        }

        $id = isset($this->types[$type]) ? $this->types[$type] : false;

        if (!$id) {
            throw new \RunTimeException(sprintf('No attached service to type named "%s"', $type));
        }

        /** @var $attributeType AttributeTypeInterface */
        $attributeType = $this->container->get($id);

        if (!$attributeType instanceof AttributeTypeInterface) {
            throw new \RunTimeException(sprintf('The service "%s" must implement "AttributeTypeInterface"', $id));
        }

        return $attributeType;
    }
}
