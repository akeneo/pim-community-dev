<?php

declare(strict_types=1);

namespace Akeneo\Asset\Bundle\Job\JobParameters\ConstraintCollectionProvider;

use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * @author Yohan Blain <yohan.blain@akeneo.com>
 */
class ComputeCompletenessOfProductsLinkedToAssets implements ConstraintCollectionProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConstraintCollection(): Collection
    {
        return new Collection(['fields' => ['asset_codes' => new NotBlank()]]);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(JobInterface $job): bool
    {
        return 'compute_completeness_of_products_linked_to_assets' === $job->getName();
    }
}
