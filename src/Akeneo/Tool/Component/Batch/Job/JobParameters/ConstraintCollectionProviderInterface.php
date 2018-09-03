<?php

namespace Akeneo\Tool\Component\Batch\Job\JobParameters;

use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Symfony\Component\Validator\Constraints\Collection;

/**
 * Constraints collection to be used to validate every parameters of a JobParameters
 *
 * For instance, to validate an export JobParameters, we can use the following Constraint,
 *
 * return new Collection([
 *   'fields' => [
 *     'filePath' => new NotBlank(['groups' => 'Execution']),
 *     'delimiter' => [
 *       new NotBlank(),
 *       new Choice([ 'choices' => [",", ";", "|"], 'message' => 'The value must be one of , or ; or |' ])
 *     ]
 *   ]
 * );
 *
 * We use the Job in the support method to determine if the Constraint can be applied.
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ConstraintCollectionProviderInterface
{
    /**
     * @return Collection
     */
    public function getConstraintCollection();

    /**
     * @return boolean
     */
    public function supports(JobInterface $job);
}
