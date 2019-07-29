<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Completeness;

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

    /**
    string(12) "Product mask"
    string(32) "sku-<all_channels>-<all_locales>"
    string(33) "name-<all_channels>-<all_locales>"
    string(23) "description-print-de_DE"
    string(23) "description-print-en_US"
    string(23) "description-print-fr_FR"
    string(36) "release_date-ecommerce-<all_locales>"
    string(43) "color_scanning-<all_channels>-<all_locales>"
     *
     *
    string(11) "Family mask"
    string(17) "price-print-en_US"
    string(15) "sku-print-en_US"
    string(26) "color_scanning-print-en_US"
    string(23) "description-print-en_US"
    string(16) "name-print-en_US"
    string(11) "Family mask"
    string(24) "description-mobile-en_US"
    string(17) "name-mobile-en_US"
    string(18) "price-mobile-en_US"
    string(27) "color_scanning-mobile-en_US"
    string(16) "sku-mobile-en_US"
    string(11) "Family mask"
    string(30) "color_scanning-ecommerce-en_US"
    string(27) "description-ecommerce-en_US"
    string(20) "name-ecommerce-en_US"
    string(21) "price-ecommerce-en_US"
    string(19) "sku-ecommerce-en_US"
     */
    public function getMask(): array
    {
        // TODO Move this for prices
        $result = [];
//        var_dump('Product mask');
        foreach ($this->rawValues as $attributeCode => $valuesByChannel) {
            foreach ($valuesByChannel as $channelCode => $valuesByLocale) {
                foreach ($valuesByLocale as $localeCode => $value) {
                    $mask = sprintf('%s-%s-%s', $attributeCode, $channelCode, $localeCode);
//                    var_dump($mask);
                    $result[] = $mask;
                }
            }
        }

        return $result;
    }

    public function getId(): int
    {
        return $this->id;
    }
}
