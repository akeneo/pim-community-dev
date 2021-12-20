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
    /** @var AttributeTypeInterface[] */
    protected $types = [];

    protected FeatureFlags $featureFlags;

    /**
     * Constructor with feature flag
     *
     * @param FeatureFlags $featureFlags
     */
    public function __construct(FeatureFlags $featureFlags)
    {
        $this->featureFlags = $featureFlags;
    }

    /**
     * Register an attribute type
     *
     * @param string                 $alias
     * @param AttributeTypeInterface $type
     *
     * @return AttributeTypeRegistry
     */
    public function register($alias, AttributeTypeInterface $type, $feature = null)
    {
        $this->types[$alias] = [
            $type,
            $feature
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
            !isset($this->types[$alias]) || (null !== $this->types[$alias][1] &&
                !$this->featureFlags->isEnabled($this->types[$alias][1])
            )
        ) {
            throw new \LogicException(sprintf('Attribute type "%s" is not registered', $alias));
        }

        return $this->types[$alias][0];
    }

    /**
     * Return the attribute types aliases
     *
     * @return array
     */
    public function getAliases()
    {
        return array_filter(
            array_keys($this->types),
            function ($alias) {
                return null === $this->types[$alias][1] || $this->featureFlags->isEnabled($this->types[$alias][1]);
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
