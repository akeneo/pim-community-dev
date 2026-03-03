<?php

namespace Akeneo\Pim\Structure\Component;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags;

/**
 * The attribute type registry
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeTypeRegistry
{
    /** @var array<string,string> */
    private $types = [];

    public function __construct(private FeatureFlags $featureFlags)
    {
    }

    /**
     * Register an attribute type
     *
     * @param string                 $alias
     * @param AttributeTypeInterface $type
     *
     * @return AttributeTypeRegistry
     */
    public function register(string $alias, AttributeTypeInterface $type, ?string $feature = null)
    {
        $this->types[$alias] = [
            'attribute_type' => $type,
            'feature' => $feature
        ];

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
        if (
            !isset($this->types[$alias]) ||
            (null !== $this->types[$alias]['feature'] && !$this->featureFlags->isEnabled($this->types[$alias]['feature']))
        ) {
            throw new \LogicException(sprintf('Attribute type "%s" is not registered', $alias));
        }

        return $this->types[$alias]['attribute_type'];
    }

    /**
     * Return the attribute types aliases
     *
     * @return array
     */
    public function getAliases()
    {
        $aliases = array_keys($this->types);

        return array_filter(
            $aliases,
            function ($alias) {
                return null === $this->types[$alias]['feature'] || $this->featureFlags->isEnabled($this->types[$alias]['feature']);
            }
        );
    }

    /**
     * Return the attribute types aliases sorted
     *
     * @return array
     */
    public function getSortedAliases()
    {
        $types = array_combine($this->getAliases(), $this->getAliases());
        asort($types);

        return $types;
    }
}
