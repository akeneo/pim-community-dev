<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Value;

use Akeneo\Pim\Enrichment\Component\Product\Model\AbstractValue;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;

/**
 * Product value for attribute types:
 *   - pim_catalog_identifier
 *   - pim_catalog_text
 *   - pim_catalog_textarea
 *   - pim_catalog_boolean
 *   - pim_catalog_number
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ScalarValue extends AbstractValue implements ValueInterface
{
    /** @var mixed */
    protected $data;

    /**
     * {@inheritdoc}
     */
    protected function __construct(string $attributeCode, $data = null, ?string $scopeCode, ?string $localeCode)
    {
        parent::__construct($attributeCode, $data, $scopeCode, $localeCode);
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        return (string) $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function isEqual(ValueInterface $value): bool
    {
        if (!$value instanceof ScalarValue) {
            return false;
        }

        return $this->getScopeCode() === $value->getScopeCode() &&
            $this->getLocaleCode() === $value->getLocaleCode() &&
            $value->getData() === $this->getData();
    }
}
