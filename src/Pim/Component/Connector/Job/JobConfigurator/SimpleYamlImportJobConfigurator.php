<?php

namespace Pim\Component\Connector\Job\JobConfigurator;

use Akeneo\Component\Batch\Job\JobConfiguratorInterface;
use Akeneo\Component\Batch\Job\JobInterface;
use Pim\Component\Catalog\Validator\Constraints\FileExtension;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

/**
 * DefaultParameters for simple YAML import
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SimpleYamlImportJobConfigurator implements JobConfiguratorInterface
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
            'uploadAllowed' => true,
            'fields' => [
                'filePath' => [
                    new NotBlank(['groups' => ['Execution', 'UploadExecution']]),
                    new FileExtension(
                        [
                            'allowedExtensions' => ['yml', 'yaml'],
                            'groups' => ['Execution', 'UploadExecution']
                        ]
                    )
                ],
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
