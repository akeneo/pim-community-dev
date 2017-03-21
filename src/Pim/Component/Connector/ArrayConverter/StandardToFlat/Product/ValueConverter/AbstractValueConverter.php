<?php

namespace Pim\Component\Connector\ArrayConverter\StandardToFlat\Product\ValueConverter;

use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\AttributeColumnsResolver;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
abstract class AbstractValueConverter implements ValueConverterInterface
{
    /** @var AttributeColumnsResolver */
    protected $columnsResolver;

    /** @var array */
    protected $supportedAttributeTypes;

    /**
     * @param AttributeColumnsResolver $columnsResolver
     * @param array                    $supportedAttributeTypes
     */
    public function __construct(AttributeColumnsResolver $columnsResolver, array $supportedAttributeTypes)
    {
        $this->columnsResolver = $columnsResolver;
        $this->supportedAttributeTypes = $supportedAttributeTypes;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsAttribute(AttributeInterface $attribute)
    {
        return in_array($attribute->getType(), $this->supportedAttributeTypes);
    }
}
