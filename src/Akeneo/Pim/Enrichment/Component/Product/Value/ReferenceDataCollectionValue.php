<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Value;

use Akeneo\Pim\Enrichment\Component\Product\Model\AbstractValue;
use Akeneo\Pim\Enrichment\Component\Product\Model\ReferenceDataInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

/**
 * Product value for a collection of reference data
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReferenceDataCollectionValue extends AbstractValue implements
    ReferenceDataCollectionValueInterface
{
    /** @var ReferenceDataInterface[] */
    protected $data;

    /**
     * @param AttributeInterface       $attribute
     * @param string                   $channel
     * @param string                   $locale
     * @param ReferenceDataInterface[] $data
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
    public function getReferenceDataCodes()
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
        $codes = array_map(function ($option) {
            return (string) $option;
        }, $this->data);

        return implode(', ', $codes);
    }

    /**
     * {@inheritdoc}
     */
    public function isEqual(ValueInterface $value)
    {
        if (!$value instanceof ReferenceDataCollectionValueInterface ||
            $this->getScope() !== $value->getScope() ||
            $this->getLocale() !== $value->getLocale()) {
            return false;
        }

        $comparedRefDataCollection = $value->getData();
        $thisRefDataCollection = $this->getData();

        if (count($comparedRefDataCollection) !== count($thisRefDataCollection)) {
            return false;
        }

        foreach ($comparedRefDataCollection as $comparedRefData) {
            $refDataFound = false;
            foreach ($thisRefDataCollection as $thisRefData) {
                if ($comparedRefData->getCode() === $thisRefData->getCode()) {
                    $refDataFound = true;
                    break;
                }
            }

            if (!$refDataFound) {
                return false;
            }
        }

        return true;
    }
}
