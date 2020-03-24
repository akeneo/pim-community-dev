<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\StandardToFlat\Product\ValueConverter;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\AttributeColumnsResolverInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
abstract class AbstractValueConverter implements ValueConverterInterface
{
    /** @var AttributeColumnsResolverInterface */
    protected $columnsResolver;

    /** @var array */
    protected $supportedAttributeTypes;

    public function __construct(AttributeColumnsResolverInterface $columnsResolver, array $supportedAttributeTypes)
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
