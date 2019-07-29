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

    public function getMask(): array
    {
        // TODO Move this for prices
        $result = [];
        foreach ($this->rawValues as $attributeCode => $valuesByChannel) {
            foreach ($valuesByChannel as $channelCode => $valuesByLocale) {
                foreach ($valuesByLocale as $localeCode => $value) {
                    $result[] = sprintf('%s-%s-%s', $attributeCode, $channelCode, $localeCode);
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
