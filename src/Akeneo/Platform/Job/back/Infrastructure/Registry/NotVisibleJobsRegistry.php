<?php
declare(strict_types=1);

namespace Akeneo\Platform\Job\Infrastructure\Registry;

use Akeneo\Tool\Component\Batch\Job\JobInterface;

/**
 * @author Grégoire Houssard <gregoire.houssard@akeneo.com>
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class NotVisibleJobsRegistry
{
    private array $notVisibleJobCodes;

    public function __construct(iterable $notVisibleJobs)
    {
        $this->notVisibleJobCodes = [];
        foreach ($notVisibleJobs as $notVisibleJob) {
            if ($notVisibleJob instanceof JobInterface) {
                $this->notVisibleJobCodes[] = $notVisibleJob->getName();
            }
        }
    }

    public function getCodes(): array
    {
        return $this->notVisibleJobCodes;
    }
}
