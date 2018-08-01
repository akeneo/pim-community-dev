<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\ValueConverter;

/**
 * Registry of converters.
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ValueConverterRegistry implements ValueConverterRegistryInterface
{
    /** @var ValueConverterInterface[] */
    protected $converters = [];

    /** @var ValueConverterInterface[] */
    protected $convertersByAttributeType = [];

    /**
     * {@inheritdoc}
     */
    public function register(ValueConverterInterface $converter)
    {
        $this->converters[] = $converter;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getConverter($attributeType)
    {
        if (isset($this->convertersByAttributeType[$attributeType])) {
            return $this->convertersByAttributeType[$attributeType];
        }

        foreach ($this->converters as $converter) {
            if ($converter->supportsField($attributeType)) {
                $this->convertersByAttributeType[$attributeType] = $converter;

                return $converter;
            }
        }

        return null;
    }
}
