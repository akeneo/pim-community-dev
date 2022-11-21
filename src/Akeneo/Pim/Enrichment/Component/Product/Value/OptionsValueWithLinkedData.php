<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Value;

use Akeneo\Pim\Enrichment\Component\Product\Model\AbstractValue;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OptionsValueWithLinkedData extends AbstractValue implements OptionsValueInterface
{
    /** @var array */
    protected $data;

    /** @var array */
    protected $linkedData;

    /**
     * {@inheritdoc}
     */
    public function __construct(
        string $attributeCode,
        ?array $data,
        ?string $scopeCode,
        ?string $localeCode,
        ?array $linkedData
    ) {
        parent::__construct($attributeCode, $data, $scopeCode, $localeCode);
        $this->linkedData = $linkedData;
    }

    /**
     * {@inheritdoc}
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @return array|null
     */
    public function getLinkedData(): ?array
    {
        return $this->linkedData;
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
    public function isEqual(ValueInterface $value): bool
    {
        if (!$value instanceof OptionsValueInterface ||
            $this->getScopeCode() !== $value->getScopeCode() ||
            $this->getLocaleCode() !== $value->getLocaleCode()
        ) {
            return false;
        }

        $comparedAttributeOptions = $value->getData();
        $thisAttributeOptions = $this->getData();

        if ($value instanceof OptionsValueWithLinkedData &&
            strcmp(json_encode($value->getLinkedData()), json_encode($this->getLinkedData()))
        ) {
            return false;
        }

        return count(array_diff($thisAttributeOptions, $comparedAttributeOptions)) === 0 &&
            count(array_diff($comparedAttributeOptions, $thisAttributeOptions)) === 0;
    }
}
