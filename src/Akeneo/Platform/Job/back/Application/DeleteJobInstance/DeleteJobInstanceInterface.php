<?php

declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Job\Application\DeleteJobInstance;

use Akeneo\Platform\Job\ServiceApi\JobInstance\DeleteJobInstance\CannotDeleteJobInstanceException;

interface DeleteJobInstanceInterface
{
    /**
     * @throws CannotDeleteJobInstanceException
     */
    public function byCodes(array $codes): void;
}
