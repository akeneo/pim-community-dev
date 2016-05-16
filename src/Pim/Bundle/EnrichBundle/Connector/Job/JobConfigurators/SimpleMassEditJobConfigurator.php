<?php

namespace Pim\Bundle\EnrichBundle\Connector\Job\JobConfigurators;

use Akeneo\Component\Batch\Job\JobConfiguratorInterface;
use Akeneo\Component\Batch\Job\JobInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Type;

/**
 * Constraints for simple mass edit
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SimpleMassEditJobConfigurator implements JobConfiguratorInterface
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
        $resolver->setDefined('filters');
        $resolver->setDefined('actions');
        $resolver->setDefault('realTimeVersioning', true);
        $resolver->setDefaults([
            'fields' => [
                'filters' => new NotNull(),
                'actions' => new NotBlank(),
                'realTimeVersioning' => new Type('bool'),
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
