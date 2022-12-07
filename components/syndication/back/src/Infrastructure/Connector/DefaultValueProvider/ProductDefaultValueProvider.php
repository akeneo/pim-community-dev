<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\Syndication\Infrastructure\Connector\DefaultValueProvider;

use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;

class ProductDefaultValueProvider implements DefaultValuesProviderInterface
{
    /** @var string[] */
    private array $supportedJobNames;

    /**
     * @param string[] $supportedJobNames
     */
    public function __construct(
        array $supportedJobNames
    ) {
        $this->supportedJobNames = $supportedJobNames;
    }

    /**
     * @return array<string, array<string, string>|string|false|null>
     */
    public function getDefaultValues(): array
    {
        $defaultValues = [
            'filePath'          => sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'export_%job_label%_%datetime%.json',
            'user_to_notify'    => null,
            'is_user_authenticated' => false,
        ];
        $defaultValues['catalogProjections'] = [];
        $defaultValues['connection'] = [
            'connectedChannelCode' => 'amazon_vendor_us',
        ];

        return $defaultValues;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(JobInterface $job)
    {
        return in_array($job->getName(), $this->supportedJobNames);
    }
}
