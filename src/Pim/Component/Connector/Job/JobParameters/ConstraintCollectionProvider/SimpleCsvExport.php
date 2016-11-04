<?php

namespace Pim\Component\Connector\Job\JobParameters\ConstraintCollectionProvider;

use Akeneo\Component\Batch\Job\JobInterface;
use Akeneo\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface;
use Pim\Bundle\ImportExportBundle\Validator\Constraints\WritableDirectory;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Type;

/**
 * Constraints for simple CSV export
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SimpleCsvExport implements ConstraintCollectionProviderInterface
{
    /** @var array */
    protected $supportedJobNames;

    /**
     * @param array $supportedJobNames
     */
    public function __construct(array $supportedJobNames)
    {
        $this->supportedJobNames = $supportedJobNames;
    }

    /**
     * {@inheritdoc}
     */
    public function getConstraintCollection()
    {
        return new Collection(
            [
                'fields' => [
                    'filePath'   => [
                        new NotBlank(['groups' => ['Execution', 'FileConfiguration']]),
                        new WritableDirectory(['groups' => ['Execution', 'FileConfiguration']]),
                        new Regex([
                            'pattern' => '/.\.csv$/',
                            'message' => 'The extension file must be ".csv"'
                        ])
                    ],
                    'delimiter'  => [
                        new NotBlank(['groups' => ['Default', 'FileConfiguration']]),
                        new Choice(
                            [
                                'choices' => [",", ";", "|"],
                                'message' => 'The value must be one of , or ; or |',
                                'groups'  => ['Default', 'FileConfiguration'],
                            ]
                        ),
                    ],
                    'enclosure'  => [
                        [
                            new NotBlank(['groups' => ['Default', 'FileConfiguration']]),
                            new Choice(
                                [
                                    'choices' => ['"', "'"],
                                    'message' => 'The value must be one of " or \'',
                                    'groups'  => ['Default', 'FileConfiguration'],
                                ]
                            ),
                        ],
                    ],
                    'withHeader' => new Type(
                        [
                            'type'   => 'bool',
                            'groups' => ['Default', 'FileConfiguration'],
                        ]
                    ),
                ],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function supports(JobInterface $job)
    {
        return in_array($job->getName(), $this->supportedJobNames);
    }
}
