<?php

namespace Pim\Component\Connector\Job\JobConfigurator;

use Akeneo\Component\Batch\Job\JobConfiguratorInterface;
use Akeneo\Component\Batch\Job\JobInterface;
use Pim\Bundle\ImportExportBundle\Validator\Constraints\WritableDirectory;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

/**
 * DefaultParameters for simple CSV export
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SimpleCsvExportJobConfigurator implements JobConfiguratorInterface
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
     * @return array
     */
    public function configure(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'filePath' => null,
            'delimiter' => ';',
            'enclosure' => '"',
            'withHeader' => true,
            'fields' => [
                'filePath' => [
                    new NotBlank(['groups' => 'Execution']),
                    new WritableDirectory(['groups' => 'Execution'])
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
