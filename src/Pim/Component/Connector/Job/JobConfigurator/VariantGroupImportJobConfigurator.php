<?php

namespace Pim\Component\Connector\Job\JobConfigurator;

use Akeneo\Component\Batch\Job\JobConfiguratorInterface;
use Akeneo\Component\Batch\Job\JobInterface;
use Akeneo\Component\Localization\Localizer\LocalizerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

/**
 * DefaultParameters for variant group CSV import
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VariantGroupImportJobConfigurator implements JobConfiguratorInterface
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
            'decimalSeparator' => LocalizerInterface::DEFAULT_DECIMAL_SEPARATOR,
            'dateFormat' => LocalizerInterface::DEFAULT_DATE_FORMAT,
            'copyValues' => true,
            'fields' => [
                'decimalSeparator' => new NotBlank(),
                'dateFormat' => new NotBlank(),
                'copyValues' => new Type('bool'),
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
