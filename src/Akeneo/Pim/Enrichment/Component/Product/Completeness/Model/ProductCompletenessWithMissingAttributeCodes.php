<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Completeness\Model;

/**
 * @author    Mathias METAYER <mathias.metayer@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ProductCompletenessWithMissingAttributeCodes
{
    /** @var string */
    private $channelCode;

    /** @var string */
    private $localeCode;

    /** @var int */
    private $requiredCount;

    /** @var string[] */
    private $missingAttributeCodes;

    public function __construct(
        string $channelCode,
        string $localeCode,
        int $requiredCount,
        array $missingAttributeCodes
    ) {
        if ($requiredCount < 0) {
            throw new \InvalidArgumentException('$requiredCount expects a positive integer');
        }

        $this->channelCode = $channelCode;
        $this->localeCode = $localeCode;
        $this->requiredCount = $requiredCount;
        $this->missingAttributeCodes = array_values($missingAttributeCodes);
    }

    public function channelCode(): string
    {
        return $this->channelCode;
    }

    public function localeCode(): string
    {
        return $this->localeCode;
    }

    public function requiredCount(): int
    {
        return $this->requiredCount;
    }

    public function missingAttributeCodes(): array
    {
        return $this->missingAttributeCodes;
    }

    public function ratio(): int
    {
        if (0 === $this->requiredCount) {
            return 100;
        }

        return (int)floor(100 * ($this->requiredCount - count($this->missingAttributeCodes)) / $this->requiredCount);
    }
}
