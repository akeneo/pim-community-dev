<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\SuggestData\Application\Proposal\Command;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class CreateProposalsCommand
{
    /** @var int */
    private $batchSize;

    /**
     * @param int $batchSize
     */
    public function __construct(int $batchSize)
    {
        if ($batchSize <= 0) {
            throw new \InvalidArgumentException('Batch size must be positive');
        }
        $this->batchSize = $batchSize;
    }

    /**
     * @return int
     */
    public function batchSize(): int
    {
        return $this->batchSize;
    }
}
