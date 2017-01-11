<?php

namespace Pim\Component\Catalog\Factory\ProductValue;

/**
 * Registry of product value factories.
 *
 * @internal  This registry contains the factories that are used by \Pim\Component\Catalog\Factory\ProductValueFactory.
 *
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductValueFactoryRegistry
{
    /** @var array */
    protected $factories;

    /**
     * @param string $attributeType
     *
     * @return ProductValueFactoryInterface
     *
     */
    public function get($attributeType)
    {
        foreach ($this->factories as $factory) {
            if ($factory->supports($attributeType)) {
                return $factory;
            }
        }

        throw new \OutOfBoundsException(sprintf(
            'No factory has been registered to create a Product Value for the attribute type "%s"',
            $attributeType
        ));
    }

    /**
     * @param ProductValueFactoryInterface $factory
     */
    public function register(ProductValueFactoryInterface $factory)
    {
        $this->factories[] = $factory;
    }
}
