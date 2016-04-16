<?php

namespace Pim\Component\Connector\Job\JobParameters\Constraints;

use Akeneo\Component\Batch\Job\JobInterface;
use Akeneo\Component\Batch\Job\JobParameters\ConstraintsInterface;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

/**
 * Constraints for product CSV import
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductCsvImport implements ConstraintsInterface
{
    /** @var SimpleCsvImport */
    protected $simpleConstraint;

    /** @var array */
    protected $supportedJobNames;

    /**
     * @param SimpleCsvImport $simpleConstraint
     * @param array           $supportedJobNames
     */
    public function __construct(SimpleCsvImport $simpleConstraint, array $supportedJobNames)
    {
        $this->simpleConstraint = $simpleConstraint;
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
        $constraintFields['enabled'] = new Type('bool');
        $constraintFields['categoriesColumn'] = new NotBlank();
        $constraintFields['familyColumn'] = new NotBlank();
        $constraintFields['groupsColumn'] = new NotBlank();
        $constraintFields['enabledComparison'] = new Type('bool');
        $constraintFields['realTimeVersioning'] = new Type('bool');

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
