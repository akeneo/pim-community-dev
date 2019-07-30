<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Completeness\Model;

/**
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Product
{
    /** @var int */
    private $id;

    /** @var string */
    private $identifier;

    /** @var string */
    private $familyCode;

    /** @var array */
    private $rawValues;

    public function __construct(
        int $id,
        string $identifier,
        string $familyCode,
        array $rawValues
    ) {
        $this->id = $id;
        $this->identifier = $identifier;
        $this->familyCode = $familyCode;
        $this->rawValues = $rawValues;
    }

    public function familyCode(): string
    {
        return $this->familyCode;
    }

    public function getMask(): array
    {
        $result = [];
        foreach ($this->rawValues as $attributeCode => $valuesByChannel) {
            foreach ($valuesByChannel as $channelCode => $valuesByLocale) {
                foreach ($valuesByLocale as $localeCode => $value) {
                    if (null !== $value) {
                        $mask = sprintf(
                            '%s-%s-%s',
                            $this->formatAttributeCode($attributeCode, $value),
                            $channelCode,
                            $localeCode
                        );
                        $result[] = $mask;
                    }
                }
            }
        }

        return $result;
    }

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * TODO Put this in a Registry to allow specific masks for new attribute types
     *
     * Case when $value is like
     * [{"amount": "2.00", "currency": "EUR"}, {"amount": "3.00", "currency": "USD"}]
     *
     * The currencies are sorted because the family masks are sorted too.
     *
     * @param string $attributeCode
     * @param mixed  $value
     *
     * @return string
     */
    private function formatAttributeCode(string $attributeCode, $value)
    {
        if (is_array($value)) {
            $isPrice = true;
            $currencies = [];
            foreach ($value as $v) {
                if (!(isset($v['amount']) && isset($v['currency']))) {
                    $isPrice = false;
                } else {
                    $currencies[] = $v['currency'];
                }
            }
            sort($currencies);

            if ($isPrice) {
                return $attributeCode . '-' . join('-', $currencies);
            }
        }

        return $attributeCode;
    }
}
