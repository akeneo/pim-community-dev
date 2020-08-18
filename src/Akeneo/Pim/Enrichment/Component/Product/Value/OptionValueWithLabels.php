<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Value;

use Akeneo\Pim\Enrichment\Component\Product\Model\AbstractValue;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OptionValueWithLabels extends AbstractValue
{
    /** @var string Option code */
    protected $data;

    /**
     * {@inheritdoc}
     */
    public function __construct(
        string $attributeCode,
        array $data,
        ?string $scopeCode,
        ?string $localeCode
    ) {
        parent::__construct($attributeCode, $data, $scopeCode, $localeCode);
    }

    /**
     * {@inheritdoc}
     */
    public function getData(): array
    {
        return $this->data;
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
