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

namespace Akeneo\Platform\Syndication\Infrastructure\Connector;

use Akeneo\Platform\Syndication\Infrastructure\Validation\Columns;
use Akeneo\Platform\Syndication\Infrastructure\Validation\ProductFilters;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface;
use Akeneo\Tool\Component\StorageUtils\Validator\Constraints\WritableDirectory;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Optional;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Constraints\Uuid;

class ConstraintCollectionProvider implements ConstraintCollectionProviderInterface
{
    /** @var string[] */
    protected array $supportedJobNames;

    /**
     * @param string[] $supportedJobNames
     */
    public function __construct(
        array $supportedJobNames
    ) {
        $this->supportedJobNames = $supportedJobNames;
    }

    /**
     * {@inheritdoc}
     */
    public function getConstraintCollection(): Assert\Collection
    {
        $constraintFields = [
            'filePath'   => [
                new NotBlank(['groups' => ['Execution', 'FileConfiguration']]),
                new WritableDirectory(['groups' => ['Execution', 'FileConfiguration']]),
                new Regex([
                    'pattern' => '/.\.json$/',
                    'message' => 'The extension file must be ".json"'
                ])
            ],
            'user_to_notify' => new Type('string'),
            'is_user_authenticated' => new Type('bool'),
        ];

        $catalogProjectionConstraint = new Assert\Collection([
            'fields' => [
                'uuid' => new Assert\Optional(new Uuid()), // MIG: should not be optional
                'code' => new Type('string'),
                'label' => new Assert\Optional(new Type('string')),
                'dataMappings' => new Columns(),
                'filters' => new ProductFilters()
            ]
        ]);

        $constraintFields['catalogProjections'] = new Assert\All([
            'constraints' => [
                new Assert\NotBlank(),
                $catalogProjectionConstraint,
            ],
        ]);
        $constraintFields['connection'] = new Assert\Optional([
            new Assert\Collection([
                'connectedChannelCode' => new Type('string'),
            ])
        ]);

        return new Assert\Collection(['fields' => $constraintFields]);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(JobInterface $job): bool
    {
        return in_array($job->getName(), $this->supportedJobNames);
    }
}
