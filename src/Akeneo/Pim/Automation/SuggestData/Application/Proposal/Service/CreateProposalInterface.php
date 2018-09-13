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

namespace Akeneo\Pim\Automation\SuggestData\Application\Proposal\Service;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
interface CreateProposalInterface
{
    /**
     * @param EntityWithValuesInterface $product
     * @param array $suggestedData
     * @param string $author
     */
    public function fromSuggestedData(EntityWithValuesInterface $product, array $suggestedData, string $author): void;
}
