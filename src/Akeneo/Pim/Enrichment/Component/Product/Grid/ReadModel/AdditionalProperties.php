<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel;

/**
 * This is a bag to add additional properties such as columns in the product datagrid.
 *
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AdditionalProperties implements \IteratorAggregate
{
    /** @var array */
    private $properties;

    /**
     * @param array $properties
     */
    public function __construct(array $properties = [])
    {
        $this->properties = (function (AdditionalProperty ...$property) {
            return $property;
        })(...$properties);
    }

    public function addAdditionalProperty(AdditionalProperty $property): AdditionalProperties
    {
        $properties = $this->properties;
        $properties[] = $property;

        return new self($properties);
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->properties);
    }
}
