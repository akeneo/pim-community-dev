<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Value;

use Akeneo\Pim\Enrichment\Component\Product\Model\AbstractValue;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;

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
     * {@inheritdoc}
     */
    protected function __construct(string $attributeCode, ?\DateTime $data, ?string $scopeCode, ?string $localeCode)
    {
        parent::__construct($attributeCode, $data, $scopeCode, $localeCode);
    }

    /**
     * {@inheritdoc}
     */
    public function getData(): ?\DateTime
    {
        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        return null !== $this->data ? $this->data->format('Y-m-d') : '';
    }

    /**
     * {@inheritdoc}
     */
    public function isEqual(ValueInterface $value): bool
    {
        if (!$value instanceof DateValueInterface ||
            $this->getScopeCode() !== $value->getScopeCode() ||
            $this->getLocaleCode() !== $value->getLocaleCode()) {
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
