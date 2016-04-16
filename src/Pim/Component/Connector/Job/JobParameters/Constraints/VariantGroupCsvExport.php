<?php

namespace Pim\Component\Connector\Job\JobParameters\Constraints;

use Akeneo\Component\Batch\Job\JobInterface;
use Akeneo\Component\Batch\Job\JobParameters\ConstraintsInterface;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Constraints for variant group CSV export
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VariantGroupCsvExport implements ConstraintsInterface
{
    /** @var SimpleCsvExport */
    protected $simpleConstraint;

    /** @var array */
    protected $supportedJobNames;

    /**
     * @param SimpleCsvExport $simpleCsv
     * @param array           $supportedJobNames
     */
    public function __construct(SimpleCsvExport $simpleCsv, array $supportedJobNames)
    {
        $this->simpleConstraint = $simpleCsv;
        $this->supportedJobNames = $supportedJobNames;
    }

    /**
     * {@inheritdoc}
     */
    public function getConstraints()
    {
        $baseConstraint = $this->simpleConstraint->getConstraints();
        $constraintFields = $baseConstraint->fields;
        $constraintFields['decimalSeparator'] = new NotBlank();
        $constraintFields['dateFormat'] = new NotBlank();

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
