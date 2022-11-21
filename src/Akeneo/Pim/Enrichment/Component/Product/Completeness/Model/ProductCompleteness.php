<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Completeness\Model;

/**
 * @author    Mathias METAYER <mathias.metayer@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ProductCompleteness
{
    /** @var string */
    private $channelCode;

    /** @var string */
    private $localeCode;

    /** @var int */
    private $requiredCount;

    /** @var int */
    private $missingCount;

    public function __construct(string $channelCode, string $localeCode, int $requiredCount, int $missingCount)
    {
        if ($requiredCount < 0) {
            throw new \InvalidArgumentException('$requiredCount expects a positive integer');
        }
        if ($missingCount < 0) {
            throw new \InvalidArgumentException('$missingCount expects a positive integer');
        }
        if ($missingCount > $requiredCount) {
            throw new \InvalidArgumentException('$requiredCount must be greater than or equal to $missingCount');
        }

        $this->channelCode = $channelCode;
        $this->localeCode = $localeCode;
        $this->requiredCount = $requiredCount;
        $this->missingCount = $missingCount;
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

    public function missingCount(): int
    {
        return $this->missingCount;
    }

    public function ratio(): int
    {
        if (0 === $this->requiredCount) {
            return 100;
        }

        return (int)floor(100 * ($this->requiredCount - $this->missingCount) / $this->requiredCount);
    }
}
