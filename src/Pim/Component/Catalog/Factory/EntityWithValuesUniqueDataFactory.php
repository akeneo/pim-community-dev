<?php

namespace Pim\Component\Catalog\Factory;

use Pim\Component\Catalog\Model\EntityWithValuesInterface;
use Pim\Component\Catalog\Model\EntityWithValuesUniqueDataInterface;
use Pim\Component\Catalog\Model\ValueInterface;

/**
 * Creates and configures a product unique data.
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EntityWithValuesUniqueDataFactory
{
    /** @var string */
    protected $entityWithValuesUniqueDataClass;

    /**
     * @param string $entityWithValuesUniqueDataClass
     */
    public function __construct($entityWithValuesUniqueDataClass)
    {
        $this->entityWithValuesUniqueDataClass = $entityWithValuesUniqueDataClass;
    }

    /**
     * @param EntityWithValuesInterface $product
     * @param ValueInterface            $value
     *
     * @return EntityWithValuesUniqueDataInterface
     */
    public function create(EntityWithValuesInterface $product, ValueInterface $value)
    {
        return new $this->entityWithValuesUniqueDataClass($product, $value);
    }
}
