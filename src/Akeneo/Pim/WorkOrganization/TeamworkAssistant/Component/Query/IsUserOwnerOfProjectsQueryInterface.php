<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Query;

/**
 * @author jmleroux <jean-marie.leroux@akeneo.com>
 */
interface IsUserOwnerOfProjectsQueryInterface
{
    public function execute(int $userId): bool;
}
