<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Platform\Bundle\ImportExportBundle\Domain;

use PhpSpec\ObjectBehavior;

class ResolveRunningUsernameSpec extends ObjectBehavior
{
    public function it_resolves_running_username_from_job_code(): void
    {
        $this->fromJobCode('my_job')->shouldReturn('job_automated_my_job');
    }
}
