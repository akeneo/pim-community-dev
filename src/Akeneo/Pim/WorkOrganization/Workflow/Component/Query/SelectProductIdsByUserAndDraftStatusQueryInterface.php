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


/**
 * @author Romain Monceau <romain@akeneo.com>
 */
interface SelectProductIdsByUserAndDraftStatusQueryInterface
{
    /**
     * @param string $username
     * @param array $draftStatuses
     *
     * @return int[]
     */
    public function execute(string $username, array $draftStatuses);
}
