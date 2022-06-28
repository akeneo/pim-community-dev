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

namespace Akeneo\Pim\WorkOrganization\Workflow\Component\Query;

use Ramsey\Uuid\UuidInterface;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
interface SelectProposalIdsFromProductUuidsQueryInterface
{
    /**
     * @param UuidInterface[] $productUuids
     * @return int[]
     */
    public function fetch(array $productUuids): array;
}
