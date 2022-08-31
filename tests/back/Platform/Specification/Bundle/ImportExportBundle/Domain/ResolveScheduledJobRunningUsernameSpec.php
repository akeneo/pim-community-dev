<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Specification\Akeneo\Platform\Bundle\ImportExportBundle\Domain;

use PhpSpec\ObjectBehavior;

class ResolveScheduledJobRunningUsernameSpec extends ObjectBehavior
{
    public function it_resolves_running_username_from_job_code(): void
    {
        $this->fromJobCode('my_job')->shouldReturn('job_automated_my_job');
    }
}
