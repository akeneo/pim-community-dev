<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel;

/**
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Rows
{
    /** @var Row[] */
    private $rows;

    /** @var int */
    private $totalCount;

    /**
     * @param Row[] $rows
     * @param int   $totalCount
     */
    public function __construct(array $rows, int $totalCount)
    {
        $this->rows = $rows;
        $this->totalCount = $totalCount;
    }

    public function rows(): array
    {
        return $this->rows;
    }

    public function totalCount(): int
    {
        return $this->totalCount;
    }
}
