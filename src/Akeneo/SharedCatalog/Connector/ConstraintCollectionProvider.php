<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\SharedCatalog\Connector;

use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Channel;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\FilterStructureLocale;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

class ConstraintCollectionProvider implements ConstraintCollectionProviderInterface
{
    /** 2MB size converted to base64 */
    private const MAX_LENGTH_BASE64 = 2000 * 1000 * 4 / 3;

    private const MAX_RECIPIENT_COUNT = 500;

    /** @var ConstraintCollectionProviderInterface */
    protected $simpleProvider;

    /** @var array */
    protected $supportedJobNames;

    public function __construct(
        ConstraintCollectionProviderInterface $simpleCsv,
        array $supportedJobNames
    ) {
        $this->simpleProvider = $simpleCsv;
        $this->supportedJobNames = $supportedJobNames;
    }

    /**
     * {@inheritdoc}
     */
    public function getConstraintCollection()
    {
        $baseConstraint = $this->simpleProvider->getConstraintCollection();
        $constraintFields = $baseConstraint->fields;
        $constraintFields['decimalSeparator'] = new NotBlank(['groups' => ['Default', 'FileConfiguration']]);
        $constraintFields['dateFormat'] = new NotBlank(['groups' => ['Default', 'FileConfiguration']]);
        $constraintFields['with_media'] = new Type(
            [
                'type' => 'bool',
                'groups' => ['Default', 'FileConfiguration'],
            ]
        );
        $constraintFields['publisher'] = new Type(['type' => ['string', 'null']]);
        $constraintFields['recipients'] = [
            new Type([
                'type' => 'array',
                'groups' => ['Default', 'FileConfiguration'],
            ]),
            new All([
                'constraints' => [
                    new Collection([
                        'fields' => [
                            'email' => [
                                new NotBlank(['groups' => ['Default', 'FileConfiguration']]),
                                new Email(['groups' => ['Default', 'FileConfiguration']]),
                            ],
                        ],
                    ]),
                ],
            ]),
            new Count(['max' => self::MAX_RECIPIENT_COUNT, 'maxMessage' => 'shared_catalog.recipients.max_limit_reached'])
        ];
        $constraintFields['branding'] = new Collection([
            'fields' => [
                'image' => [
                    new Type(['type' => ['string', 'null']]),
                    new Length(['max' => self::MAX_LENGTH_BASE64, 'maxMessage' => 'shared_catalog.branding.filesize_too_large'])
                ]
            ]
        ]);
        $constraintFields['filters'] = [
            new Collection(
                [
                    'fields' => [
                        'structure' => [
                            new FilterStructureLocale(['groups' => ['Default', 'DataFilters']]),
                            new Collection(
                                [
                                    'fields' => [
                                        'locales' => new NotBlank(['groups' => ['Default', 'DataFilters']]),
                                        'scope' => new Channel(['groups' => ['Default', 'DataFilters']]),
                                        'attributes' => new Type(
                                            [
                                                'type' => 'array',
                                                'groups' => ['Default', 'DataFilters'],
                                            ]
                                        ),
                                    ],
                                    'allowMissingFields' => true,
                                ]
                            ),
                        ],
                    ],
                    'allowExtraFields' => true,
                ]
            ),
        ];

        return new Collection(['fields' => $constraintFields]);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(JobInterface $job)
    {
        return in_array($job->getName(), $this->supportedJobNames);
    }
}
