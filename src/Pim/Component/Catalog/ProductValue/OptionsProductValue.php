<?php

namespace Pim\Component\Catalog\ProductValue;

use Pim\Component\Catalog\Model\AbstractProductValue;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\AttributeOptionInterface;

/**
 * Product value for "pim_catalog_multiselect" attribute type
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OptionsProductValue extends AbstractProductValue implements OptionsProductValueInterface
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
}
