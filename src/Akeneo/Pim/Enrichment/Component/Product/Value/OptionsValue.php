<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Value;

use Akeneo\Pim\Enrichment\Component\Product\Model\AbstractValue;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;

/**
 * Product value for "pim_catalog_multiselect" attribute type
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OptionsValue extends AbstractValue implements OptionsValueInterface
{
    /** @var string[] Options codes */
    protected $data;

    /**
     * {@inheritdoc}
     */
    protected function __construct(string $attributeCode, ?array $data, ?string $scopeCode, ?string $localeCode)
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
    public function hasCode(string $code): bool
    {
        return in_array($code, $this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function getOptionCodes(): array
    {
        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        $optionStrings = [];

        foreach ($this->data as $optionCode) {
            $optionStrings[] = '['.$optionCode.']';
        }

        return implode(', ', $optionStrings);
    }

    /**
     * {@inheritdoc}
     */
    public function isEqual(ValueInterface $value): bool
    {
        if (!$value instanceof OptionsValueInterface ||
            $this->getScopeCode() !== $value->getScopeCode() ||
            $this->getLocaleCode() !== $value->getLocaleCode()) {
            return false;
        }

        $comparedAttributeOptions = $value->getData();
        $thisAttributeOptions = $this->getData();

        return count(array_diff($thisAttributeOptions, $comparedAttributeOptions)) === 0 &&
            count(array_diff($comparedAttributeOptions, $thisAttributeOptions)) === 0;
    }
}
