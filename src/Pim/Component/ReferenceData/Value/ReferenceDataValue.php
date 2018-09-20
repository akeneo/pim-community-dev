<?php

namespace Pim\Component\ReferenceData\Value;

use Pim\Component\Catalog\Model\AbstractValue;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ValueInterface;
use Pim\Component\ReferenceData\Model\ReferenceDataInterface;

/**
 * Product value for a reference data
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReferenceDataValue extends AbstractValue implements ReferenceDataValueInterface
{
    /** @var ReferenceDataInterface|null */
    protected $data;

    /**
     * @param AttributeInterface          $attribute
     * @param string                      $channel
     * @param string                      $locale
     * @param ReferenceDataInterface|null $data
     */
    public function __construct(AttributeInterface $attribute, $channel, $locale, ReferenceDataInterface $data = null)
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
        return null !== $this->data ? (string) $this->data : '';
    }

    /**
     * {@inheritdoc}
     */
    public function isEqual(ValueInterface $value)
    {
        if (!$value instanceof ReferenceDataValueInterface ||
            $this->getScope() !== $value->getScope() ||
            $this->getLocale() !== $value->getLocale()) {
            return false;
        }

        $thisData = $this->getData();
        $comparedData = $value->getData();

        if (null === $comparedData && null === $thisData) {
            return true;
        }
        if (null === $comparedData || null === $thisData) {
            return false;
        }

        return $thisData->getCode() === $comparedData->getCode();
    }
}
