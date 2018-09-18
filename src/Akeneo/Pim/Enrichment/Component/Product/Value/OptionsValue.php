<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Value;

use Akeneo\Pim\Enrichment\Component\Product\Model\AbstractValue;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;

/**
 * Product value for "pim_catalog_multiselect" attribute type
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OptionsValue extends AbstractValue implements OptionsValueInterface
{
    /** @var AttributeOptionInterface[] */
    protected $data;

    /**
     * @param AttributeInterface         $attribute
     * @param string                     $channel
     * @param string                     $locale
     * @param AttributeOptionInterface[] $data
     */
    public function __construct(AttributeInterface $attribute, $channel, $locale, array $data = [])
    {
        $this->setAttribute($attribute);
        $this->setScope($channel);
        $this->setLocale($locale);

        $this->data = $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function hasCode($code)
    {
        foreach ($this->data as $option) {
            if ($option->getCode() === $code) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getOptionCodes()
    {
        $options = [];
        foreach ($this->data as $option) {
            $options[] = $option->getCode();
        }

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        $optionValues = [];
        foreach ($this->data as $option) {
            $optionValue = $option->getOptionValue();
            $optionValues[] = null !== $optionValue ? $optionValue->getValue() : '['.$option->getCode().']';
        }

        return implode(', ', $optionValues);
    }

    /**
     * {@inheritdoc}
     */
    public function isEqual(ValueInterface $value)
    {
        if (!$value instanceof OptionsValueInterface ||
            $this->getScope() !== $value->getScope() ||
            $this->getLocale() !== $value->getLocale()) {
            return false;
        }

        $comparedAttributeOptions = $value->getData();
        $thisAttributeOptions = $this->getData();

        if (count($comparedAttributeOptions) !== count($thisAttributeOptions)) {
            return false;
        }

        foreach ($comparedAttributeOptions as $comparedAttributeOption) {
            $hasEqual = false;
            foreach ($thisAttributeOptions as $thisAttributeOption) {
                if ($thisAttributeOption->getCode() === $comparedAttributeOption->getCode()) {
                    $hasEqual = true;

                    break;
                }
            }

            if (!$hasEqual) {
                return false;
            }
        }

        return true;
    }
}
