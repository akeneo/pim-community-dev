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


namespace Akeneo\Pim\WorkOrganization\Workflow\Component\Factory;

use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\DraftSource;
use Akeneo\UserManagement\Component\Model\UserInterface;

/**
 * @author Olivier Pontier <olivier.pontier@akeneo.com>
 */
class PimUserDraftSourceFactory
{
    const PIM_SOURCE_CODE = 'pim';
    const PIM_SOURCE_LABEL = 'PIM';

    public function createFromUser(UserInterface $user): DraftSource
    {
        return new DraftSource(
            self::PIM_SOURCE_CODE,
            self::PIM_SOURCE_LABEL,
            $user->getUserIdentifier(),
            $user->getFullName()
        );
    }
}
