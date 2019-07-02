<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Component\Query;

use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\Projection\PublishedProductCompleteness;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
interface GetPublishedProductCompletenesses
{
    /**
     * @param int $publishedProductId
     *
     * @return PublishedProductCompleteness[]
     */
    public function fromPublishedProductId(int $publishedProductId): array;
}
