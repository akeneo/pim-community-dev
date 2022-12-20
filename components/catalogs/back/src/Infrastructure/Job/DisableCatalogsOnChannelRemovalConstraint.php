<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Job;

use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Required;

class DisableCatalogsOnChannelRemovalConstraint implements ConstraintCollectionProviderInterface
{
    public function getConstraintCollection(): Collection
    {
        return new Collection([
            'fields' => [
                'channel_codes' => new Required([
                    new Assert\Type('array'),
                    new Assert\All([
                        'constraints' => [
                            new Assert\Type('string'),
                        ],
                    ]),
                ]),
            ],
        ]);
    }

    public function supports(JobInterface $job): bool
    {
        return $job->getName() === 'disable_catalogs_on_channel_removal';
    }
}
