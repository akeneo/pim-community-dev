<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\Job\JobParameters\ConstraintCollectionProvider;

use Akeneo\Channel\Component\Validator\Constraint\ActivatedLocale;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Channel;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\FilterStructureLocale;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Constraints for product model CSV export
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductModelCsvExport implements ConstraintCollectionProviderInterface
{
    /** @var ConstraintCollectionProviderInterface */
    private $simpleProvider;

    /** @var array */
    private $supportedJobNames;

    /**
     * @param ConstraintCollectionProviderInterface $simpleCsv
     * @param array                                 $supportedJobNames
     */
    public function __construct(ConstraintCollectionProviderInterface $simpleCsv, array $supportedJobNames)
    {
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
        $constraintFields['with_label'] = new Type(
            [
                'type'   => 'bool',
                'groups' => ['Default', 'FileConfiguration'],
            ]
        );
        $constraintFields['header_with_label'] = new Type(
            [
                'type'   => 'bool',
                'groups' => ['Default', 'FileConfiguration'],
            ]
        );
        $constraintFields['file_locale'] = [
            new ActivatedLocale(['groups' => ['Default', 'FileConfiguration']]),
            new Callback(function ($value, ExecutionContextInterface $context) {
                $fields = $context->getRoot();
                if (true === $fields['with_label'] && empty($value)) {
                    $context
                        ->buildViolation('The locale cannot be empty.')
                        ->addViolation();
                }
            })
        ];
        $constraintFields['with_media'] = new Type(
            [
                'type'   => 'bool',
                'groups' => ['Default', 'FileConfiguration'],
            ]
        );
        $constraintFields['filters'] = [
            new Collection(
                [
                    'fields'           => [
                        'structure' => [
                            new FilterStructureLocale(['groups' => ['Default', 'DataFilters']]),
                            new Collection(
                                [
                                    'fields'             => [
                                        'locales'    => new NotBlank(['groups' => ['Default', 'DataFilters']]),
                                        'scope'      => new Channel(['groups' => ['Default', 'DataFilters']]),
                                        'attributes' => new Type(
                                            [
                                                'type'   => 'array',
                                                'groups' => ['Default', 'DataFilters'],
                                            ]
                                        )
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
