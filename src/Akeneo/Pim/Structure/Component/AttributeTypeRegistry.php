<?php

namespace Akeneo\Pim\Structure\Component;

/**
 * The attribute type registry
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeTypeRegistry
{
    /** @var AttributeTypeInterface[] */
    protected $types = [];

    /**
     * Register an attribute type
     *
     * @param string                 $alias
     * @param AttributeTypeInterface $type
     *
     * @return AttributeTypeRegistry
     */
    public function register($alias, AttributeTypeInterface $type)
    {
        $this->types[$alias] = $type;

        return $this;
    }

    /**
     * Return the attribute type
     *
     * @param string $alias
     *
     * @throws \LogicException
     *
     * @return AttributeTypeInterface
     */
    public function get($alias)
    {
        if (!isset($this->types[$alias])) {
            throw new \LogicException(sprintf('Attribute type "%s" is not registered', $alias));
        }

        return $this->types[$alias];
    }

    /**
     * Return the attribute types aliases
     *
     * @return array
     */
    public function getAliases()
    {
        return array_keys($this->types);
    }

    /**
     * Return the attribute types aliases sorted
     *
     * @return array
     */
    public function getSortedAliases()
    {
        $types = array_combine(array_keys($this->types), array_keys($this->types));
        asort($types);

        return $types;
    }
}
