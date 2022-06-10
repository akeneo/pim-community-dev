<?php

declare(strict_types=1);

/*
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\Connector;

use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;

class DefaultValueProvider implements DefaultValuesProviderInterface
{
    public function __construct(
        private DefaultValuesProviderInterface $overriddenProvider,
        /** @var string[] */
        private array $supportedJobNames,
    ) {
    }

    /**
     * @return string[]
     */
    public function getDefaultValues(): array
    {
        $defaultValues = $this->overriddenProvider->getDefaultValues();
        $defaultValues['storage'] = [
            'type' => 'none',
        ];

        return $defaultValues;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(JobInterface $job): bool
    {
        return in_array($job->getName(), $this->supportedJobNames);
    }
}
