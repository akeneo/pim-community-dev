<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Job;

use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Required;
use Symfony\Component\Validator\Constraints\Type;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DisableCatalogsOnAttributeOptionRemovalConstraint implements ConstraintCollectionProviderInterface
{
    public function getConstraintCollection(): Collection
    {
        return new Collection([
            'fields' => [
                'attribute_code' => new Required([
                    new Type('string'),
                ]),
                'attribute_option_code' => new Required([
                    new Type('string'),
                ]),
            ],
        ]);
    }

    public function supports(JobInterface $job): bool
    {
        return $job->getName() === 'disable_catalogs_on_attribute_option_removal';
    }
}
