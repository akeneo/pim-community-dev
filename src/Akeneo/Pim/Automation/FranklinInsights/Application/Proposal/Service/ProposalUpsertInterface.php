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

namespace Akeneo\Pim\Automation\FranklinInsights\Application\Proposal\Service;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Proposal\ValueObject\ProposalSuggestedData;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
interface ProposalUpsertInterface
{
    /**
     * Creates or updates a proposal given a set of values.
     *
     * @param ProposalSuggestedData[] $suggestedData
     * @param string $author
     */
    public function process(array $suggestedData, string $author): void;
}
