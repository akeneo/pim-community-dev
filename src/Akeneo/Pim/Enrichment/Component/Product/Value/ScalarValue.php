<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Value;

use Akeneo\Pim\Enrichment\Component\Product\Model\AbstractValue;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

/**
 * Product value for attribute types:
 *   - pim_catalog_identifier
 *   - pim_catalog_text
 *   - pim_catalog_textarea
 *   - pim_catalog_boolean
 *   - pim_catalog_number
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ScalarValue extends AbstractValue implements ValueInterface
{
    /** @var mixed */
    protected $data;

    /**
     * @param AttributeInterface $attribute
     * @param string             $channel
     * @param string             $locale
     * @param mixed              $data
     */
    public function __construct(AttributeInterface $attribute, $channel, $locale, $data)
    {
        $this->setAttribute($attribute);
        $this->setScope($channel);
        $this->setLocale($locale);

        $this->data = $data;
    }

    /**
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return (string) $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function isEqual(ValueInterface $value)
    {
        if (!$value instanceof ScalarValue) {
            return false;
        }

        return $this->getScope() === $value->getScope() &&
            $this->getLocale() === $value->getLocale() &&
            $value->getData() === $this->getData();
    }
}
