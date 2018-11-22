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

namespace Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\Connector;

use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\ReferenceEntityQuery;

interface FindConnectorReferenceEntityItemsInterface
{
    public function __invoke(ReferenceEntityQuery $query): array;
}
