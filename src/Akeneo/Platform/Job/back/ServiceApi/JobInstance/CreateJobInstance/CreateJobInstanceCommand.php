<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\ServiceApi\JobInstance\CreateJobInstance;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class CreateJobInstanceCommand
{
    public function __construct(
        public string $type,
        public string $code,
        public string $label,
        public string $connector,
        public string $jobName,
        public array $rawParameters,
        public bool $isVisible = true,
    ) {
    }
}
