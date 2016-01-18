<?php

namespace Pim\Bundle\TransformBundle\Transformer\Property;

use Pim\Component\ReferenceData\ConfigurationRegistryInterface;

/**
 * Reference data transformer
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReferenceDataTransformer implements PropertyTransformerInterface
{
    /** @var ConfigurationRegistryInterface */
    protected $registry;

    /**
     * Constructor
     *
     * @param ConfigurationRegistryInterface $registry
     */
    public function __construct(ConfigurationRegistryInterface $registry = null)
    {
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value, array $options = [])
    {
        if (null === $this->registry) {
            return null;
        }

        $value = trim($value);
        if (empty($value)) {
            return null;
        }

        if (!$this->registry->has($value)) {
            $references = array_keys($this->registry->all());
            throw new \InvalidArgumentException(sprintf(
                'Reference data "%s" does not exist. Allowed values are: %s',
                $value,
                implode(', ', $references)
            ));
        }

        return $value;
    }
}
