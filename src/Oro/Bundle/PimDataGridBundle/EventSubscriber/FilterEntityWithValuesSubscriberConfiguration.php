<?php

declare(strict_types=1);

namespace Oro\Bundle\PimDataGridBundle\EventSubscriber;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

/**
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FilterEntityWithValuesSubscriberConfiguration
{
    /** @var AttributeInterface[] */
    protected $attributeCodes = [];

    /** @var boolean */
    protected $filterEntityWithValues;

    /**
     * @param AttributeInterface[] $attributeCodes
     * @param bool                 $filterEntityWithValues
     */
    private function __construct(array $attributeCodes, bool $filterEntityWithValues)
    {
        $this->attributeCodes = $attributeCodes;
        $this->filterEntityWithValues = $filterEntityWithValues;
    }

    /**
     * Hydrate entity with only the values belonging to a list of attribute codes.
     * WARNING: Should only be used when loading entities for the datagrid.
     *
     * @param array $attributeCodes
     *
     * @return FilterEntityWithValuesSubscriberConfiguration
     */
    public static function filterEntityValues(array $attributeCodes): FilterEntityWithValuesSubscriberConfiguration
    {
        return new self($attributeCodes, true);
    }

    /**
     * Hydrates entity with values with all the values.
     *
     * @return FilterEntityWithValuesSubscriberConfiguration
     */
    public static function doNotFilterEntityValues(): FilterEntityWithValuesSubscriberConfiguration
    {
        return new self([], false);
    }

    /**
     * @return array
     */
    public function attributeCodesToFilterEntityValues(): array
    {
        return $this->attributeCodes;
    }

    /**
     * @return bool
     */
    public function shouldFilterEntityValues(): bool
    {
        return $this->filterEntityWithValues;
    }
}
