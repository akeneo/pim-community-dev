<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Value;

use Akeneo\Pim\Enrichment\Component\Product\Model\AbstractValue;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OptionValueWithLinkedData extends AbstractValue implements OptionValueInterface
{
    /** @var string Option code */
    protected $data;

    /** @var array */
    protected $linkedData;

    /**
     * {@inheritdoc}
     */
    public function __construct(
        string $attributeCode,
        ?string $data,
        ?string $scopeCode,
        ?string $localeCode,
        ?array $linkedData
    ) {
        parent::__construct($attributeCode, $data, $scopeCode, $localeCode);
        $this->linkedData = $linkedData;
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
    public function getData(): ?string
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
        if (!$value instanceof OptionValueInterface ||
            $this->getScopeCode() !== $value->getScopeCode() ||
            $this->getLocaleCode() !== $value->getLocaleCode()
        ) {
            return false;
        }

        if ($value instanceof OptionValueWithLinkedData && $this->getLinkedData() !== $value->getLinkedData()) {
            return false;
        }

        return $this->getData() === $value->getData();
    }
}
