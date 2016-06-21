<?php

namespace Pim\Component\Connector\ArrayConverter\StandardToFlat\Product\ValueConverter;

use Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\AttributeColumnsResolver;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
abstract class AbstractValueConverter
{
    /** @var AttributeColumnsResolver */
    protected $columnsResolver;

    /** @var array */
    protected $supportedFieldType;

    /**
     * @param AttributeColumnsResolver $columnsResolver
     */
    public function __construct(AttributeColumnsResolver $columnsResolver)
    {
        $this->columnsResolver = $columnsResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsAttribute($attributeType)
    {
        return in_array($attributeType, $this->supportedFieldType);
    }

    /**
     * @param string $attributeCode
     * @param mixed  $data
     *
     * @return array
     */
    abstract public function convert($attributeCode, $data);
}
