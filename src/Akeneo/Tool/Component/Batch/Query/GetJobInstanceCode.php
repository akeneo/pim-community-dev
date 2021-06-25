<?php
declare(strict_types=1);

namespace Akeneo\Tool\Component\Batch\Query;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
interface GetJobInstanceCode
{
    public function fromJobExecutionId(int $jobExecutionId): ?string;
}
