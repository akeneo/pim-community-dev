<?php

namespace Akeneo\Component\Batch\Job;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Configure a Job options
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface JobConfiguratorInterface
{
    /**
     * @return array
     */
    public function configure(OptionsResolver $resolver);

    /**
     * @return boolean
     */
    public function supports(JobInterface $job);
}
