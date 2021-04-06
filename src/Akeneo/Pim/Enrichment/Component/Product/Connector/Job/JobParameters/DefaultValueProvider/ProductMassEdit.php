<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\Job\JobParameters\DefaultValueProvider;

use Akeneo\Pim\Enrichment\Component\ContextOrigin;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;

/**
 * DefaultParameters for product mass edit
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductMassEdit implements DefaultValuesProviderInterface
{
    protected array $supportedJobNames;

    public function __construct(array $supportedJobNames)
    {
        $this->supportedJobNames = $supportedJobNames;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultValues(): array
    {
        return [
            'filters'               => [],
            'actions'               => [],
            'realTimeVersioning'    => true,
            'user_to_notify'        => null,
            'is_user_authenticated' => false,
            'origin'                => ContextOrigin::MASS_EDIT,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supports(JobInterface $job): bool
    {
        return in_array($job->getName(), $this->supportedJobNames);
    }
}
