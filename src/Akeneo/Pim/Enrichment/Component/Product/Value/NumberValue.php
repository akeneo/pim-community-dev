<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Value;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NumberValue extends ScalarValue
{
    public function isEqual(ValueInterface $value): bool
    {
        if (!$value instanceof NumberValue) {
            return false;
        }

        if ($this->getScopeCode() !== $value->getScopeCode() || $this->getLocaleCode() !== $value->getLocaleCode()) {
            return false;
        }

        if ($this->getData() === $value->getData()) {
            return true;
        }

        try {
            return 0 === \bccomp($this->formatData($this->data), $this->formatData($value->getData()), 50);
        } catch (\Throwable) {
        }

        return false;
    }

    private function formatData($data): string
    {
        if (\is_int($data) || \is_float($data) || (\is_string($data) && \is_numeric($data))) {
            return \number_format(floatval($data), 50, '.', '');
        }

        return (string) $data;
    }
}
