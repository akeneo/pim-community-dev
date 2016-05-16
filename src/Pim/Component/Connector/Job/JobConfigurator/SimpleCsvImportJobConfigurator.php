<?php

namespace Pim\Component\Connector\Job\JobConfigurator;

use Akeneo\Component\Batch\Job\JobConfiguratorInterface;
use Akeneo\Component\Batch\Job\JobInterface;
use Pim\Component\Catalog\Validator\Constraints\FileExtension;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

/**
 * DefaultParameters for simple CSV import
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SimpleCsvImportJobConfigurator implements JobConfiguratorInterface
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
    public function configure(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'filePath' => null,
            'delimiter' => ';',
            'enclosure' => '"',
            'escape' => '\\',
            'withHeader' => true,
            'uploadAllowed' => true,
            'fields' => [
                'filePath' => [
                    new NotBlank(['groups' => ['Execution', 'UploadExecution']]),
                    new FileExtension(
                        [
                            'allowedExtensions' => ['csv', 'zip'],
                            'groups' => ['Execution', 'UploadExecution']
                        ]
                    )
                ],
                'delimiter' => [
                    new NotBlank(),
                    new Choice(
                        [
                            'choices' => [",", ";", "|"],
                            'message' => 'The value must be one of , or ; or |'
                        ]
                    )
                ],
                'enclosure' => [
                    [
                        new NotBlank(),
                        new Choice(
                            [
                                'choices' => ['"', "'"],
                                'message' => 'The value must be one of " or \''
                            ]
                        )
                    ]
                ],
                'withHeader' => new Type('bool'),
                'escape' => new NotBlank(),
                'uploadAllowed' => [
                    new Type('bool'),
                    new IsTrue(['groups' => 'UploadExecution']),
                ]
            ]
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(JobInterface $job)
    {
        return in_array($job->getName(), $this->supportedJobNames);
    }
}
