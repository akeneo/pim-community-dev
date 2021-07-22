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

namespace Akeneo\Platform\TailoredExport\Infrastructure\Connector;

use Akeneo\Platform\TailoredExport\Infrastructure\Validation\ChannelShouldExist;
use Akeneo\Platform\TailoredExport\Infrastructure\Validation\Columns;
use Akeneo\Platform\TailoredExport\Infrastructure\Validation\LocaleShouldBeActive;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;

class ConstraintCollectionProvider implements ConstraintCollectionProviderInterface
{
    protected ConstraintCollectionProviderInterface $simpleProvider;

    /** @var string[] */
    protected array $supportedJobNames;

    /**
     * @param string[] $supportedJobNames
     */
    public function __construct(
        ConstraintCollectionProviderInterface $simpleProvider,
        array $supportedJobNames
    ) {
        $this->simpleProvider = $simpleProvider;
        $this->supportedJobNames = $supportedJobNames;
    }

    /**
     * {@inheritdoc}
     */
    public function getConstraintCollection(): Assert\Collection
    {
        $baseConstraint = $this->simpleProvider->getConstraintCollection();
        $constraintFields = $baseConstraint->fields;
        $constraintFields['columns'] = new Columns();
        $constraintFields['filters'] = new Assert\Collection([
            'fields' => [
                'data' => [
                    new Assert\Type('array'),
                    new Assert\All(['constraints' => [
                        new Assert\Collection([
                            'fields' => [
                                'field' => new Assert\NotBlank(),
                                'operator' => new Assert\NotBlank(),
                                'context' => new Assert\Optional([
                                    new Assert\Collection([
                                        'fields' => [
                                            'scope' => new Assert\Optional(new ChannelShouldExist()),
                                            'locale' => new Assert\Optional(),
                                            'locales' => new Assert\Optional(
                                                [
                                                    new Assert\All(new LocaleShouldBeActive()),
                                                    new Assert\NotBlank(),
                                                ]
                                            ),
                                            'channel' => new Assert\Optional(new ChannelShouldExist())
                                        ],
                                    ]),
                                ]),
                            ],
                            'allowExtraFields' => true,
                        ])
                    ]])
                ]
            ]
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
