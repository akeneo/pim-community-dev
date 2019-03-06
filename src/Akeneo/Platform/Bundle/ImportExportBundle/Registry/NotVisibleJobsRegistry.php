<?php
declare(strict_types=1);

namespace Akeneo\Platform\Bundle\ImportExportBundle\Registry;

use Akeneo\Tool\Component\Batch\Job\JobInterface;

/**
 * Registry for jobs that you don't want to see, for example in grid or widget.
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NotVisibleJobsRegistry
{
    /** @var string[] */
    private $notVisibleJobCodes;

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
