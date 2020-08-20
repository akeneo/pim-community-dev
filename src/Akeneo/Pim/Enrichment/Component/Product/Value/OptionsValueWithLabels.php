<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Value;

use Akeneo\Pim\Enrichment\Component\Product\Model\AbstractValue;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OptionsValueWithLabels extends AbstractValue
{
    /** @var array */
    protected $data;

    /** @var array */
    protected $linked_data;

    /**
     * {@inheritdoc}
     */
    public function __construct(
        string $attributeCode,
        ?array $data,
        ?string $scopeCode,
        ?string $localeCode,
        ?array $linked_data
    ) {
        parent::__construct($attributeCode, $data, $scopeCode, $localeCode);
        $this->linked_data = $linked_data;
    }

    /**
     * {@inheritdoc}
     */
    public function getData(): ?array
    {
        return $this->data;
    }

    /**
     * @return array|null
     */
    public function getLinkedData(): ?array {
        return $this->linked_data;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        return null !== $this->data ? '['.$this->data.']' : '';
    }

    /**
     * {@inheritdoc}
     */
    public function isEqual(ValueInterface $value): bool
    {
        if (!$value instanceof OptionValueWithLabels ||
            $this->getScopeCode() !== $value->getScopeCode() ||
            $this->getLocaleCode() !== $value->getLocaleCode()) {
            return false;
        }

        // useless for POC but inaccurate
        return $this->getData() === $value->getData();
    }
}
