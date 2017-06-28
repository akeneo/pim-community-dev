<?php

namespace Pim\Component\Catalog\Value;

use Pim\Component\Catalog\Model\AbstractValue;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\AttributeOptionInterface;

/**
 * Product value for "pim_catalog_simpleselect" attribute type
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OptionValue extends AbstractValue implements OptionValueInterface
{
    /** @var AttributeOptionInterface[] */
    protected $data;

    /**
     * @param AttributeInterface            $attribute
     * @param string                        $channel
     * @param string                        $locale
     * @param AttributeOptionInterface|null $data
     */
    public function __construct(
        AttributeInterface $attribute,
        $channel,
        $locale,
        AttributeOptionInterface $data = null
    ) {
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
    public function __toString()
    {
        if (null !== $option = $this->getData()) {
            $optionValue = $option->getOptionValue();

            return null !== $optionValue ? $optionValue->getValue() : '['.$option->getCode().']';
        }

        return '';
    }
}
