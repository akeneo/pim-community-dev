<?php

namespace Pim\Bundle\EnrichBundle\Connector\Job\JobParameters\Constraints;

use Akeneo\Component\Batch\Job\JobInterface;
use Akeneo\Component\Batch\Job\JobParameters\ConstraintsInterface;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

/**
 * Constraints for product quick export
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductQuickExport implements ConstraintsInterface
{
    /** @var ConstraintsInterface */
    protected $simpleConstraint;

    /** @var array */
    protected $supportedJobNames;

    /**
     * @param ConstraintsInterface $simple
     * @param array                $supportedJobNames
     */
    public function __construct(ConstraintsInterface $simple, array $supportedJobNames)
    {
        $this->simpleConstraint = $simple;
        $this->supportedJobNames = $supportedJobNames;
    }

    /**
     * {@inheritdoc}
     */
    public function getConstraints()
    {
        $baseConstraint = $this->simpleConstraint->getConstraints();
        $constraintFields = $baseConstraint->fields;
        $constraintFields['filters'] = new NotBlank(['groups' => 'Execution']);
        $constraintFields['mainContext'] = new NotBlank(['groups' => 'Execution']);

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
