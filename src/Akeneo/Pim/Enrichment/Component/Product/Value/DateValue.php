<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Value;

use Akeneo\Pim\Enrichment\Component\Product\Model\AbstractValue;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

/**
 * Product value for "pim_catalog_date" attribute types
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DateValue extends AbstractValue implements DateValueInterface
{
    /** @var \DateTime */
    protected $data;

    /**
     * @param AttributeInterface $attribute
     * @param string             $channel
     * @param string             $locale
     * @param \DateTime|null     $data
     */
    public function __construct(AttributeInterface $attribute, $channel, $locale, \DateTime $data = null)
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
    public function __toString()
    {
        return null !== $this->data ? $this->data->format('Y-m-d') : '';
    }

    /**
     * {@inheritdoc}
     */
    public function isEqual(ValueInterface $value)
    {
        if (!$value instanceof DateValueInterface ||
            $this->getScope() !== $value->getScope() ||
            $this->getLocale() !== $value->getLocale()) {
            return false;
        }

        $thisDate = $this->getData();
        $comparedDate = $value->getData();

        if (null === $thisDate && null === $comparedDate) {
            return true;
        }
        if (null === $thisDate || null === $comparedDate) {
            return false;
        }

        return $thisDate->getTimestamp() === $comparedDate->getTimestamp() &&
            $thisDate->getTimezone()->getName() === $comparedDate->getTimezone()->getName();
    }
}
