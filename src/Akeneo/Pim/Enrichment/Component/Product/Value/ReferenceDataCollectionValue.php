<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Value;

use Akeneo\Pim\Enrichment\Component\Product\Model\AbstractValue;
use Akeneo\Pim\Enrichment\Component\Product\Model\ReferenceDataInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;

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

    protected function __construct(string $attributeCode, ?array $data = [], ?string $scopeCode, ?string $localeCode)
    {
        if (null === $data) {
            $data = [];
        }
        parent::__construct($attributeCode, $data, $scopeCode, $localeCode);
    }

    /**
     * {@inheritdoc}
     */
    public function getData(): ?array
    {
        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function getReferenceDataCodes() : array
    {
        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        $refCodeStrings = [];

        foreach ($this->data as $refCode) {
            $refCodeStrings[] = '['.$refCode.']';
        }
        return implode(', ', $refCodeStrings);
    }

    /**
     * {@inheritdoc}
     */
    public function isEqual(ValueInterface $value): bool
    {
        if (!$value instanceof ReferenceDataCollectionValueInterface ||
            $this->getScopeCode() !== $value->getScopeCode() ||
            $this->getLocaleCode() !== $value->getLocaleCode()) {
            return false;
        }

        $comparedRefDataCollection = $value->getData();
        $thisRefDataCollection = $this->getData();

        return count(array_diff($thisRefDataCollection, $comparedRefDataCollection)) === 0 &&
            count(array_diff($comparedRefDataCollection, $thisRefDataCollection)) === 0;
    }
}
