<?php

namespace Akeneo\Component\Batch\Job\JobParameters;

use Akeneo\Component\Batch\Job\JobInterface;
use Symfony\Component\Validator\Constraints\Collection;

/**
 * Empty constraints that may be used to validate any simple JobParameters
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DefaultConstraintCollectionProvider implements ConstraintCollectionProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConstraintCollection()
    {
        return new Collection(['fields' => []]);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(JobInterface $job)
    {
        return true;
    }
}
