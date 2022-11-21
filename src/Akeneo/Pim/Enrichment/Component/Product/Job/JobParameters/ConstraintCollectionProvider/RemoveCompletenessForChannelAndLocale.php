<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Job\JobParameters\ConstraintCollectionProvider;

use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Type;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class RemoveCompletenessForChannelAndLocale implements ConstraintCollectionProviderInterface
{
    /** @var array */
    private $supportedJobNames;

    public function __construct(array $supportedJobNames)
    {
        $this->supportedJobNames = $supportedJobNames;
    }

    public function getConstraintCollection(): Collection
    {
        return new Collection(
            [
                'fields' => [
                    'channel_code' => new NotNull(),
                    'locales_identifier' => [
                        new NotNull(),
                        new Type('array'),
                        new All([
                            new NotBlank(),
                        ])
                    ],
                    'username' => new NotNull(),
                ],
            ]
        );
    }

    public function supports(JobInterface $job): bool
    {
        return in_array($job->getName(), $this->supportedJobNames);
    }
}
